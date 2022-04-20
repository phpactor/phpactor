<?php

namespace Phpactor\Extension\LanguageServerPsalm\DiagnosticProvider;

use Amp\Promise;
use Phpactor\Extension\LanguageServerPsalm\Model\Linter;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;

class PsalmDiagnosticProvider implements DiagnosticsProvider
{
    private Linter $linter;

    public function __construct(Linter $linter)
    {
        $this->linter = $linter;
    }

    
    public function provideDiagnostics(TextDocumentItem $textDocument): Promise
    {
        return $this->linter->lint($textDocument->uri, $textDocument->text);
    }

    public function name(): string
    {
        return 'psalm';
    }
}
