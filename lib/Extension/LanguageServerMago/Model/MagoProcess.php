<?php

namespace Phpactor\Extension\LanguageServerMago\Model;

use Amp\ByteStream\StreamException;
use Amp\CancellationToken;
use Amp\Process\ProcessException;
use Amp\Promise;
use Amp\TimeoutException;
use Phpactor\Amp\Process\ProcessBuilder;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Psr\Log\LoggerInterface;
use Throwable;
use function Amp\ByteStream\buffer;
use function Amp\call;
use function Amp\Promise\timeout;

/**
 * Runs `mago <subcommand> --reporting-format=json --stdin-input <relative path>`
 * with the document text piped on stdin, and parses the result.
 *
 * Mago is a native (Rust) binary, so the command is run directly without a PHP
 * interpreter prefix. Global options such as --config must precede the
 * subcommand.
 */
class MagoProcess
{
    public function __construct(
        private string $cwd,
        private MagoConfig $config,
        private LoggerInterface $logger,
        private DiagnosticsParser $parser = new DiagnosticsParser(),
    ) {
    }

    /**
     * @return Promise<array<Diagnostic>>
     */
    public function analyse(
        string $subcommand,
        string $source,
        string $relativePath,
        string $documentUri,
        string $documentText,
        CancellationToken $cancel,
    ): Promise {
        return call(function () use ($subcommand, $source, $relativePath, $documentUri, $documentText, $cancel) {
            $process = ProcessBuilder::create($this->buildArgs($subcommand, $relativePath))
                ->cwd($this->cwd)
                ->mergeParentEnv()
                ->build();

            $start = microtime(true);
            yield $process->start();

            // Honour client cancellation by killing the process.
            $cancelId = $cancel->subscribe(function () use ($process): void {
                if ($process->isRunning()) {
                    $process->kill();
                }
            });

            try {
                // Buffer both streams before writing stdin and joining, so the
                // child can drain its stdout while we feed it (avoiding a pipe
                // deadlock) and large output cannot stall the process.
                $stdoutPromise = buffer($process->getStdout());
                $stderrPromise = buffer($process->getStderr());

                try {
                    $stdin = $process->getStdin();
                    yield $stdin->write($documentText);
                    yield $stdin->end();

                    /** @var int $exitCode */
                    $exitCode = yield timeout($process->join(), $this->config->timeout());
                } catch (TimeoutException) {
                    if ($process->isRunning()) {
                        $process->kill();
                    }
                    $this->logger->error(sprintf(
                        'Mago timed out after %dms: %s',
                        $this->config->timeout(),
                        $process->getCommand(),
                    ));

                    return [];
                } catch (ProcessException | StreamException) {
                    // join() rejects, or stdin closes, when the process is
                    // killed, which happens when the client cancels the request.
                    $this->logger->debug(sprintf(
                        'Mago run cancelled: %s',
                        $process->getCommand(),
                    ));

                    return [];
                }

                $stdout = yield $stdoutPromise;

                // A clean file produces empty output (issues are reported via
                // JSON only when present), so empty stdout is not an error.
                if (!is_string($stdout) || trim($stdout) === '') {
                    $this->logger->debug(sprintf(
                        'Mago produced no diagnostics in %ss (exit %s): %s',
                        number_format(microtime(true) - $start, 4),
                        $exitCode,
                        $process->getCommand(),
                    ));

                    return [];
                }

                try {
                    return $this->parser->parse($stdout, $documentText, $source, $relativePath, $documentUri);
                } catch (Throwable $error) {
                    $stderr = yield $stderrPromise;
                    $this->logger->error(sprintf(
                        'Mago output could not be parsed (exit %s): %s; error: %s; stderr: %s',
                        $exitCode,
                        $process->getCommand(),
                        $error->getMessage(),
                        is_string($stderr) ? trim($stderr) : '',
                    ));

                    return [];
                }
            } finally {
                $cancel->unsubscribe($cancelId);
            }
        });
    }

    /**
     * @return list<string>
     */
    private function buildArgs(string $subcommand, string $relativePath): array
    {
        $args = [$this->config->bin()];

        // Global options precede the subcommand.
        if (null !== $this->config->config()) {
            $args[] = '--config';
            $args[] = $this->config->config();
        }

        $args[] = $subcommand;
        $args[] = '--reporting-format=json';
        $args[] = '--stdin-input';
        $args[] = $relativePath;

        return $args;
    }
}
