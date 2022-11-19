<?php

namespace Phpactor\Extension\LanguageServerPsalm\Model;

use Amp\Process\Process;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\Diagnostic;
use function Amp\ByteStream\buffer;
use Psr\Log\LoggerInterface;

class PsalmProcess
{
    private DiagnosticsParser $parser;

    public function __construct(
        private string $cwd,
        private PsalmConfig $config,
        private LoggerInterface $logger,
        DiagnosticsParser $parser = null
    ) {
        $this->parser = $parser ?: new DiagnosticsParser();
    }

    /**
     * @return Promise<array<Diagnostic>>
     */
    public function analyse(string $filename): Promise
    {
        return \Amp\call(function () use ($filename) {
            $process = new Process([
                $this->config->psalmBin(),
                '--no-cache',
                '--show-info=true',
                '--output-format=json'
            ], $this->cwd);

            $start = microtime(true);
            $pid = yield $process->start();

            $stdout = yield buffer($process->getStdout());
            $stderr = yield buffer($process->getStderr());

            $exitCode = yield $process->join();

            if ($exitCode !== 0 && $exitCode !== 2) {
                $this->logger->error(sprintf(
                    'Psalm exited with code "%s": %s',
                    $exitCode,
                    $stderr
                ));

                return [];
            }

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
