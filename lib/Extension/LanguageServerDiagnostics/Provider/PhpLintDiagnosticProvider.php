<?php

namespace Phpactor\Extension\LanguageServerDiagnostics\Provider;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\Extension\LanguageServerDiagnostics\Model\PhpLinter;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;
use function Amp\call;

class PhpLintDiagnosticProvider implements DiagnosticsProvider
{
    public function __construct(
        private PhpLinter $linter,
        private TextDocumentLocator $locator
    ) {
    }


    public function provideDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument) {
            return $this->linter->lint(
                $this->locator->get(TextDocumentUri::fromString($textDocument->uri))
            );
        });
    }

    public function name(): string
    {
        return 'php-lint';
    }
}
