<?php

namespace Phpactor\Extension\LanguageServer\DiagnosticProvider;

use Amp\CancellationToken;
use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\TextDocument\TextDocumentUri;
use Webmozart\Glob\Glob;

class PathExcludingDiagnosticsProvider implements DiagnosticsProvider
{
    /**
     * @param list<string> $paths
     */
    public function __construct(
        private readonly DiagnosticsProvider $innerProvider,
        private readonly array $paths
    ) {
    }

    public function provideDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        foreach ($this->paths as $glob) {
            if (true === Glob::match(TextDocumentUri::fromString($textDocument->uri)->path(), $glob)) {
                return new Success([]);
            }
        }
        return $this->innerProvider->provideDiagnostics($textDocument, $cancel);
    }

    public function name(): string
    {
        return $this->innerProvider->name();
    }
}
