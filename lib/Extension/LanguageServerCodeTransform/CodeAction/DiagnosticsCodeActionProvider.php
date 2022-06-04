<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;

use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;

class DiagnosticsCodeActionProvider implements DiagnosticsProvider, CodeActionProvider
{
    public function provideDiagnostics(TextDocumentItem $textDocument): Promise
    {
    }

    public function name(): string
    {
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range): Promise
    {
    }

    public function kinds(): array
    {
    }
}
