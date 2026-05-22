<?php

namespace Phpactor\Extension\LanguageServer\CodeAction;

use Amp\CancellationToken;
use Amp\CancellationTokenSource;
use Amp\CombinedCancellationToken;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;

class ThereCanOnlyBeOneCodeActionProvider implements CodeActionProvider
{
    private ?CancellationTokenSource $cancel = null;

    public function __construct(private CodeActionProvider $inner)
    {
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        if ($this->cancel) {
            $this->cancel->cancel();
        }

        $this->cancel = new CancellationTokenSource();

        return $this->inner->provideActionsFor($textDocument, $range, new CombinedCancellationToken(
            $cancel,
            $this->cancel->getToken(),
        ));
    }

    public function kinds(): array
    {
        return $this->inner->kinds();
    }

    public function describe(): string
    {
        return $this->inner->describe();
    }
}
