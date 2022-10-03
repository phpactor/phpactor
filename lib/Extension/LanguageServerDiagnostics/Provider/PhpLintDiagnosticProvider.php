<?php

namespace Phpactor\Extension\LanguageServerDiagnostics\Provider;

use function Amp\call;
use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\Extension\LanguageServerDiagnostics\Model\PhpLinter;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;

class PhpLintDiagnosticProvider implements DiagnosticsProvider
{
    private TextDocumentLocator $locator;

    private PhpLinter $linter;

    public function __construct(PhpLinter $linter, TextDocumentLocator $locator)
    {
        $this->locator = $locator;
        $this->linter = $linter;
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
