<?php

namespace Phpactor\Extension\LanguageServerMago\Provider;

use Amp\CancellationToken;
use Amp\Promise;
use Amp\Success;
use Phpactor\Extension\LanguageServerMago\Model\Linter;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;

/**
 * Publishes Mago diagnostics for a document. One instance is registered per Mago
 * subcommand (analyze -> "mago", lint -> "mago-lint"); each can be toggled
 * independently.
 */
class MagoDiagnosticProvider implements DiagnosticsProvider
{
    public function __construct(
        private Linter $linter,
        private string $name,
        private bool $enabled = true,
    ) {
    }

    public function provideDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        if (!$this->enabled) {
            return new Success([]);
        }

        return $this->linter->lint($textDocument->uri, $textDocument->text, $cancel);
    }

    public function name(): string
    {
        return $this->name;
    }
}
