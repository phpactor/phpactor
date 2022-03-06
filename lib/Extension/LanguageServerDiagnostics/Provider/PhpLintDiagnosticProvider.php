<?php

namespace Phpactor\Extension\LanguageServerDiagnostics\Provider;

use Amp\Promise;
use Phpactor\Extension\LanguageServerDiagnostics\Model\PhpLinter;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\TextDocument\TextDocumentLocator;
use Phpactor\TextDocument\TextDocumentUri;
use function Amp\call;

class PhpLintDiagnosticProvider implements DiagnosticsProvider
{
    /**
     * @var TextDocumentLocator
     */
    private $locator;

    /**
     * @var PhpLinter
     */
    private $linter;


    public function __construct(PhpLinter $linter, TextDocumentLocator $locator)
    {
        $this->locator = $locator;
        $this->linter = $linter;
    }

    /**
     * {@inheritDoc}
     */
    public function provideDiagnostics(TextDocumentItem $textDocument): Promise
    {
        return call(function () use ($textDocument) {
            return $this->linter->lint(
                $this->locator->get(TextDocumentUri::fromString($textDocument->uri))
            );
        });
    }
}
