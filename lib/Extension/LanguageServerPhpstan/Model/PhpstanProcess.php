<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use function Amp\call;
use Amp\Process\Process;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\Diagnostic;
use function Amp\ByteStream\buffer;
use Psr\Log\LoggerInterface;

class PhpstanProcess
{
    private DiagnosticsParser $parser;

    public function __construct(
        private string $cwd,
        private PhpstanConfig $config,
        private LoggerInterface $logger,
        ?DiagnosticsParser $parser = null
    ) {
        $this->parser = $parser ?: new DiagnosticsParser();
    }

    /**
     * @return Promise<array<Diagnostic>>
     */
    public function analyseInPlace(string $filename): Promise
    {
        $args = [
            PHP_BINARY,
            $this->config->phpstanBin(),
            'analyse',
            '--no-progress',
            '--error-format=json',
            $filename,
        ];

        return $this->runPhpstan($args);
    }

    /**
     * @return Promise<array<Diagnostic>>
     */
    public function editorModeAnalyse(string $filename, string $tempFile): Promise
    {
        $args = [
            PHP_BINARY,
            $this->config->phpstanBin(),
            'analyse',
            '--no-progress',
            '--error-format=json',
            '--tmp-file='.$tempFile,
            '--instead-of='.$filename,
            $filename
        ];

        return $this->runPhpstan($args);
    }

    /**
     * @return Promise<string|null>
     */
    public function version(): Promise
    {
        return call(function () {
            $args = [
                PHP_BINARY,
                $this->config->phpstanBin(),
                '--version',
            ];
            $process = new Process($args, $this->cwd);
            yield $process->start();
            $exitCode = yield $process->join();

            if ($exitCode !== 0) {
                return null;
            }

            $stdout = yield buffer($process->getStdout());

            if (!is_string($stdout)) {
                return null;
            }

            preg_match('{[0-9]+\.[0-9]+\.[0-9]+}', $stdout, $matches);

            return $matches[0] ?? null;
        });
    }

    /**
    * @param array<string> $args
    *
    * @return Promise<array<Diagnostic>>
    */
    private function runPhpstan(array $args): Promise
    {
        return call(function () use ($args) {
            if (null !== $this->config->level()) {
                $args[] = '--level=' . (string)$this->config->level();
            }
            if (null !== $this->config->config()) {
                $args[] = '--configuration=' . (string)$this->config->config();
            }
            if (null !== $this->config->memLimit()) {
                $args[] = '--memory-limit=' . (string)$this->config->memLimit();
            }
            $process = new Process($args, $this->cwd);

            $start = microtime(true);
            $pid = yield $process->start();

            $stdout = buffer($process->getStdout());
            $stderr = buffer($process->getStderr());

            $exitCode = yield $process->join();

            if ($exitCode > 1) {
                $this->logger->error(sprintf(
                    'Phpstan exited with code "%s": %s',
                    $exitCode,
                    yield $stderr
                ));

                return [];
            }

            $this->logger->debug(sprintf(
                'Phpstan completed in %s: %s in %s',
                number_format(microtime(true) - $start, 4),
                $process->getCommand(),
                $process->getWorkingDirectory(),
            ));

            $stdout = yield $stdout;
            if ($stdout === '') {
                $this->logger->error(sprintf(
                    'Phpstan exited with code "%s": But the standard output was empty',
                    $exitCode,
                ));
                return [];
            }

            return $this->parser->parse($stdout, $this->config->severity());
        });
    }
}
