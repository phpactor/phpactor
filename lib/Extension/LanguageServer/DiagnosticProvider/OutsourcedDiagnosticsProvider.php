<?php

namespace Phpactor\Extension\LanguageServer\DiagnosticProvider;

use Amp\CancellationToken;
use Amp\Process\Process;
use Amp\Process\ProcessException;
use Amp\Promise;
use Phpactor\Amp\Process\ProcessUtil;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Psr\Log\LoggerInterface;
use RuntimeException;
use function Amp\ByteStream\buffer;
use function Amp\call;

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
            $pid = yield $process->start();

            ProcessUtil::killAfter($this->logger, $process, $this->timeout);

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
