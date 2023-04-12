<?php

namespace Phpactor\Extension\LanguageServer\DiagnosticProvider;

use Amp\CancellationToken;
use Amp\Process\Process;
use Amp\Process\ProcessException;
use Amp\Process\StatusError;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Psr\Log\LoggerInterface;
use RuntimeException;
use function Amp\ByteStream\buffer;
use function Amp\asyncCall;
use function Amp\call;
use function Amp\delay;

class OutsourcedDiagnosticsProvider implements DiagnosticsProvider
{
    /**
     * @param list<string> $command
     */
    public function __construct(
        private array $command,
        private string $cwd,
        private LoggerInterface $logger,
        private int $timeout = 5,
    ) {
    }

    public function provideDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument) {
            $process = new Process(array_merge($this->command, [
                '--uri=' . $textDocument->uri
            ]), $this->cwd);
            $start = time();
            $pid = yield $process->start();

            asyncCall(function () use ($process, $start) {
                while ($process->isRunning()) {
                    yield delay(500);
                    // phpstan doesn't expect that $process->isRunning() output can change
                    // @phpstan-ignore-next-line
                    if (time() >= $start + $this->timeout && $process->isRunning()) {
                        try {
                            $process->kill();
                            $this->logger->warning(sprintf('Killed diagnostics process "%s" because it lived longer than %ds', $process->getPid(), $this->timeout));
                        } catch (StatusError $e) {
                        }
                        break;
                    }
                }
            });

            $stdin = $process->getStdin();

            yield $stdin->write($textDocument->text);
            $stdin->close();
            $json = yield buffer($process->getStdout());

            try {
                $exitCode = yield $process->join();
            } catch (ProcessException $e) {
                $this->logger->warning($e->getMessage());
                return [];
            }
            if ($exitCode !== 0) {
                throw new RuntimeException(sprintf(
                    'Phpactor diagnostics process exited with code "%s": %s',
                    $exitCode,
                    yield buffer($process->getStderr())
                ));
            }
            $array = json_decode($json, true);
            if (!is_array($array)) {
                throw new RuntimeException(sprintf(
                    'Could not decode JSON: %s',
                    $json
                ));
            }

            return array_map(fn (array $diagnostic) => Diagnostic::fromArray($diagnostic), $array);
        });
    }

    public function name(): string
    {
        return 'outsourced';
    }
}
