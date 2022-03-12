<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Provider;

use Amp\Promise;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Exception;

class PhpstanDiagnosticProvider implements DiagnosticsProvider
{
    /**
     * @var Linter
     */
    private $linter;
    private $supported = null;

    public function __construct(Linter $linter)
    {
        $this->linter = $linter;
    }

    /**
     * {@inheritDoc}
     */
    public function provideDiagnostics(TextDocumentItem $textDocument): Promise
    {
        try {
            return $this->linter->lint($textDocument->uri, $textDocument->text);
        } catch (Exception $e) {
            dump($e);
        }
    }
}
