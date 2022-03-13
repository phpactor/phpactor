<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Provider;

use Amp\Promise;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Exception;

class PhpstanDiagnosticProvider implements DiagnosticsProvider
{
    private Linter $linter;

    private $supported = null;

    public function __construct(Linter $linter)
    {
        $this->linter = $linter;
    }

    
    public function provideDiagnostics(TextDocumentItem $textDocument): Promise
    {
        try {
            return $this->linter->lint($textDocument->uri, $textDocument->text);
        } catch (Exception $e) {
            dump($e);
        }
    }
}
