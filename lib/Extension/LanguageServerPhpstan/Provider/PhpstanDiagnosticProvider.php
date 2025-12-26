<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Provider;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;

class PhpstanDiagnosticProvider implements DiagnosticsProvider
{
    public function __construct(private readonly Linter $linter)
    {
    }

    public function provideDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        return $this->linter->lint($textDocument->uri, $textDocument->text);
    }

    public function name(): string
    {
        return 'phpstan';
    }
}
