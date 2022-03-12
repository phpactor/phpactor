<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Provider;

use Amp\Promise;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;

class PhpstanDiagnosticProvider implements DiagnosticsProvider
{
    /**
     * @var Linter
     */
    private $linter;

    public function __construct(Linter $linter)
    {
        $this->linter = $linter;
    }

    /**
     * {@inheritDoc}
     */
    public function provideDiagnostics(TextDocumentItem $textDocument): Promise
    {
        return $this->linter->lint($textDocument->uri, $textDocument->text);
    }
}
