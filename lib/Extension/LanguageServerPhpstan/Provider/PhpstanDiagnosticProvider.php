<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Provider;

use Amp\Promise;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;

class PhpstanDiagnosticProvider implements DiagnosticsProvider
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
        return 'phpstan';
    }
}
