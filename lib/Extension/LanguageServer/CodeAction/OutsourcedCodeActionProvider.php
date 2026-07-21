<?php

namespace Phpactor\Extension\LanguageServer\CodeAction;

use Amp\ByteStream\StreamException;
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
use function Amp\delay;

class OutsourcedCodeActionProvider implements CodeActionProvider
{
    /**
     * @param list<string> $command
     */
    public function __construct(
        private array $command,
        private string $cwd,
        private LoggerInterface $logger,
        private CodeActionProvider $providerInfo,
        private int $timeout = 5,
    ) {
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument, $range, $cancel) {
            $process = new Process(array_merge([
                PHP_BINARY
            ], $this->command, [
                json_encode(new CodeActionParams(
                    ProtocolFactory::textDocumentIdentifier($textDocument->uri),
                    $range,
                    new CodeActionContext([]),
                ), JSON_THROW_ON_ERROR),
                sprintf('--config-extra=%s', sprintf('{"%s": false}', WorseReflectionExtension::PARAM_ENABLE_CONTEXT_LOCATION))
            ]), $this->cwd);

            /** @var int $pid */
            $pid = yield $process->start();

            ProcessUtil::killAfter($this->logger, $process, $this->timeout);

            $stdin = $process->getStdin();

            asyncCall(function () use ($process, $cancel, $pid) {
                while ($process->isRunning()) {
                    if ($cancel->isRequested()) {
                        $process->kill();
                        $this->logger->info(sprintf(
                            'Killing code-action process "%s" as requested',
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

            /** @var string $json */
            $json = yield buffer($process->getStdout());

            try {
                /** @var int $exitCode */
                $exitCode = yield $process->join();
            } catch (ProcessException $e) {
                $this->logger->warning(sprintf(
                    'Code action resolver took too long to analyse file or was culled to make way for a new request (timed-out after %s seconds)',
                    $this->timeout,
                ));
                return [];
            }
            if ($exitCode !== 0) {
                /** @var string $stderr */
                $stderr = yield buffer($process->getStderr());

                throw new RuntimeException(sprintf(
                    'Phpactor code-action process exited with code "%s": %s',
                    $exitCode,
                    $stderr
                ));
            }

            $array = json_decode($json, true);

            if (!is_array($array)) {
                throw new RuntimeException(sprintf(
                    'Could not decode JSON: %s',
                    $json
                ));
            }

            /** @phpstan-ignore-next-line */
            return array_map(fn (array $codeAction) => CodeAction::fromArray($codeAction), $array);
        });
    }

    public function kinds(): array
    {
        return $this->providerInfo->kinds();
    }

    public function describe(): string
    {
        return sprintf('outsourced: %s', $this->providerInfo->describe());
    }
}
