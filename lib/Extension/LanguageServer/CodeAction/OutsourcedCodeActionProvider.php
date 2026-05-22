<?php

namespace Phpactor\Extension\LanguageServer\CodeAction;

use Amp\CancellationToken;
use Amp\Process\Process;
use Amp\Process\ProcessException;
use Amp\Promise;
use Phpactor\Amp\Process\ProcessUtil;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\CodeActionContext;
use Phpactor\LanguageServerProtocol\CodeActionParams;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Psr\Log\LoggerInterface;
use RuntimeException;
use function Amp\ByteStream\buffer;
use function Amp\asyncCall;
use function Amp\call;
use function Amp\async;
use function Amp\delay;

class OutsourcedCodeActionProvider implements CodeActionProvider
{
    /**
     * @param list<string> $command
     * @param list<CodeActionProvider> $providers
     */
    public function __construct(
        private array $command,
        private string $cwd,
        private LoggerInterface $logger,
        private int $timeout = 5,
    ) {
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument, $range, $cancel) {
            $process = new Process(array_merge([PHP_BINARY], $this->command, [
                json_encode(new CodeActionParams(
                    ProtocolFactory::textDocumentIdentifier($textDocument->uri),
                    $range,
                    new CodeActionContext([]),
                )),
                sprintf('--config-extra=%s', sprintf('{"%s": false}', WorseReflectionExtension::PARAM_ENABLE_CONTEXT_LOCATION))
            ]), $this->cwd);
            $pid = yield $process->start();

            ProcessUtil::killAfter($this->logger, $process, $this->timeout);

            $stdin = $process->getStdin();

            asyncCall(function () use ($process, $cancel, $pid) {
                while ($process->isRunning()) {
                    if ($cancel->isRequested()) {
                        $process->kill();
                        $this->logger->info(sprintf(
                            'Killing process code-action process "%s" as requested',
                            $pid,
                        ));
                    }
                    yield delay(0.5);
                }
            });

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
                    'Phpactor code-action process exited with code "%s": %s',
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

            return array_map(fn (array $codeAction) => CodeAction::fromArray($codeAction), $array);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function kinds(): array
    {
        return [];
    }

    public function describe(): string
    {
        return sprintf('aggregate code action proivder with %s providers', count($this->providers));
    }
}

