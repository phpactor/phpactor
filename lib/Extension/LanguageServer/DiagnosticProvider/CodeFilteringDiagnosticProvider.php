<?php

namespace Phpactor\Extension\LanguageServer\DiagnosticProvider;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use function Amp\call;

class CodeFilteringDiagnosticProvider implements DiagnosticsProvider
{
    /**
     * @var array<string,int>
     */
    private array $ignoreCodes;

    /**
     * @param list<string> $ignoreCodes
     */
    public function __construct(private DiagnosticsProvider $innerProvider, array $ignoreCodes)
    {
        $this->ignoreCodes = array_flip($ignoreCodes);
    }

    public function provideDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        return call(function () use ($cancel, $textDocument) {
            return array_values(array_filter(yield $this->innerProvider->provideDiagnostics($textDocument, $cancel), function (Diagnostic $diagnostic) {
                return null === $diagnostic->code || !array_key_exists(
                    $diagnostic->code,
                    $this->ignoreCodes
                );
            }));
        });
    }

    public function name(): string
    {
        return $this->innerProvider->name();
    }
}
