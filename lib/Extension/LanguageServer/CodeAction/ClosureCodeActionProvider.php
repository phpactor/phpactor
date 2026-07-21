<?php

namespace Phpactor\Extension\LanguageServer\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Closure;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;

class ClosureCodeActionProvider implements CodeActionProvider
{
    /**
     * @param Closure(TextDocumentItem,Range,CancellationToken):Promise<array<CodeAction>> $closure
     */
    public function __construct(private Closure $closure)
    {
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return ($this->closure)($textDocument, $range, $cancel);
    }

    public function kinds(): array
    {
        return [];
    }

    public function describe(): string
    {
        return 'closure';
    }
}
