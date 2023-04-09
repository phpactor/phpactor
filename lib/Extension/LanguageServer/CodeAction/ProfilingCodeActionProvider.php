<?php

namespace Phpactor\Extension\LanguageServer\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Psr\Log\LoggerInterface;
use Throwable;
use function Amp\call;

class ProfilingCodeActionProvider implements CodeActionProvider
{
    public function __construct(private CodeActionProvider $innerProvider, private LoggerInterface $logger)
    {
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument, $range, $cancel) {
            $start = microtime(true);
            $this->logger->info(sprintf('PROF        >> code-action [%s]', $this->innerProvider::class));
            try {
                $result = yield $this->innerProvider->provideActionsFor($textDocument, $range, $cancel);
                $elapsed = microtime(true) - $start;
            } catch (Throwable $e) {
                $elapsed = microtime(true) - $start;
                $this->logger->info(sprintf('PROF %-6s << code-action [%s] ERR: [%s] %s', number_format($elapsed, 4), $this->innerProvider::class, $e::class, $e->getMessage()));
                throw $e;
            }
            $this->logger->info(sprintf('PROF %-6s << code-action [%s]', number_format($elapsed, 4), $this->innerProvider::class));
            return $result;
        });
    }

    public function kinds(): array
    {
        return $this->innerProvider->kinds();
    }
}
