<?php

namespace Phpactor\Extension\LanguageServer\DiagnosticProvider;

use Amp\ByteStream\StreamException;
use Amp\CancellationToken;
use Amp\Process\Process;
use Amp\Process\ProcessException;
use Amp\Promise;
use Phpactor\Amp\Process\ProcessUtil;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
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
        return call(function () use ($textDocument, $cancel) {
            $process = new Process(array_merge([PHP_BINARY], $this->command, [
                '--uri=' . $textDocument->uri,
                sprintf('--config-extra=%s', sprintf('{"%s": false}', WorseReflectionExtension::PARAM_ENABLE_CONTEXT_LOCATION))
            ]), $this->cwd);
            $pid = yield $process->start();
            assert(is_int($pid));

            ProcessUtil::killAfter($this->logger, $process, $this->timeout);

            $stdin = $process->getStdin();

            asyncCall(function () use ($process, $cancel, $pid) {
                while ($process->isRunning()) {
                    if ($cancel->isRequested()) {
                        $process->kill();
                        $this->logger->info(sprintf(
                            'Killing diagnostics process "%s" as requested',
                            $pid,
                        ));
                    }
                    yield delay(500);
                }
            });

            try {
                yield $stdin->write($textDocument->text);
                $stdin->close();
            } catch (StreamException $exception) {
                $this->logger->debug(sprintf(
                    'Could not write to stdin: %s',
                    $exception->getMessage(),
                ));

                return [];
            }

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
