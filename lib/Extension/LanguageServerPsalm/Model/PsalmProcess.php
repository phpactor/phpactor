<?php

namespace Phpactor\Extension\LanguageServerPsalm\Model;

use Amp\Process\Process;
use Amp\Process\ProcessException;
use Amp\Promise;
use Phpactor\Amp\Process\ProcessUtil;
use Phpactor\LanguageServerProtocol\Diagnostic;
use RuntimeException;
use function Amp\ByteStream\buffer;
use Psr\Log\LoggerInterface;

class PsalmProcess
{
    private DiagnosticsParser $parser;

    public function __construct(
        private string $cwd,
        private PsalmConfig $config,
        private LoggerInterface $logger,
        DiagnosticsParser $parser = null,
        private int $timeoutSeconds = 10,
    ) {
        $this->parser = $parser ?: new DiagnosticsParser();
    }

    /**
     * @return Promise<array<Diagnostic>>
     */
    public function analyse(string $filename): Promise
    {
        return \Amp\call(function () use ($filename) {
            $command = [
                PHP_BINARY,
                $this->config->psalmBin(),
                sprintf(
                    '--show-info=%s',
                    $this->config->shouldShowInfo() ? 'true' : 'false',
                ),
                '--output-format=json',
            ];

            $command = (function (array $command, ?int $errorLevel) {
                if (null === $errorLevel) {
                    return $command;
                }
                $command[] = sprintf('--error-level=%d', $errorLevel);
                return $command;
            })($command, $this->config->errorLevel());

            $command = (function (array $command, ?int $threads) {
                if (null === $threads) {
                    return $command;
                }
                $command[] = sprintf('--threads=%d', $threads);
                return $command;
            })($command, $this->config->threads());

            if (!$this->config->useCache()) {
                $command[] = '--no-cache';
            }
            $command[] = $filename;

            $process = new Process($command, $this->cwd);

            $start = microtime(true);
            $pid = yield $process->start();

            ProcessUtil::killAfter($this->logger, $process, $this->timeoutSeconds);

            try {
                $exitCode = yield $process->join();
            } catch (ProcessException $e) {
                return [];
            }

            if ($exitCode !== 0 && $exitCode !== 2) {
                throw new RuntimeException(
                    'Psalm exited with code "%s": %s',
                    $exitCode,
                    yield buffer($process->getStderr())
                );
            }

            $stdout = yield buffer($process->getStdout());

            $this->logger->debug(sprintf(
                'Psalm completed in %s: %s in %s ... checking for %s',
                number_format(microtime(true) - $start, 4),
                $process->getCommand(),
                $process->getWorkingDirectory(),
                $filename
            ));

            return $this->parser->parse($stdout, $filename);
        });
    }
}
