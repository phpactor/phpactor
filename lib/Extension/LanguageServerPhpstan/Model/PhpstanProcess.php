<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Model;

use function Amp\ByteStream\buffer;
use Amp\Process\Process;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Psr\Log\LoggerInterface;

class PhpstanProcess
{
    private DiagnosticsParser $parser;

    private string $cwd;

    private LoggerInterface $logger;

    private string $phpstanBin;

    private PhpstanConfig $config;

    public function __construct(
        string $cwd,
        PhpstanConfig $config,
        LoggerInterface $logger,
        DiagnosticsParser $parser = null
    ) {
        $this->parser = $parser ?: new DiagnosticsParser();
        $this->cwd = $cwd;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @return Promise<array<Diagnostic>>
     */
    public function analyse(string $filename): Promise
    {
        return \Amp\call(function () use ($filename) {
            $args = [
                $this->config->phpstanBin(),
                'analyse',
                '--no-progress',
                '--error-format=json',
                $filename
            ];

            if (null !== $this->config->level()) {
                $args[] = '--level=' . (string)$this->config->level();
            }
            $process = new Process($args, $this->cwd);

            $start = microtime(true);
            $pid = yield $process->start();

            $stdout = yield buffer($process->getStdout());
            $stderr = yield buffer($process->getStderr());

            $exitCode = yield $process->join();

            if ($exitCode > 1) {
                $this->logger->error(sprintf(
                    'Phpstan exited with code "%s": %s',
                    $exitCode,
                    $stderr
                ));

                return [];
            }

            $this->logger->debug(sprintf(
                'Phpstan completed in %s: %s in %s',
                number_format(microtime(true) - $start, 4),
                $process->getCommand(),
                $process->getWorkingDirectory(),
            ));

            return $this->parser->parse($stdout);
        });
    }
}
