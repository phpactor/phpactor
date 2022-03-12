<?php

namespace Phpactor\Extension\LanguageServer\Diagnostic;

use Amp\Promise;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;

class ContextuallyActiveDiagnosticProvider implements DiagnosticsProvider
{
    private array $providers;

    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * {@inheritDoc}
     */
    public function provideDiagnostics(TextDocumentItem $textDocument): Promise
    {
        $providers = yield $this->filterProviders($this->providers);
    }
}
