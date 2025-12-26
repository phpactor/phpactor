<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Domain\Transformers;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\Extension\LanguageServerCodeTransform\Converter\DiagnosticsConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\TransformCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use function Amp\call;

class TransformerCodeActionPovider implements DiagnosticsProvider, CodeActionProvider
{
    public function __construct(
        private readonly Transformers $transformers,
        private readonly string $name,
        private readonly string $title
    ) {
    }


    public function kinds(): array
    {
        return [
            $this->kind()
        ];
    }


    public function provideDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        return $this->getDiagnostics($textDocument);
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument) {
            $diagnostics = yield $this->getDiagnostics($textDocument);
            if (0 === count($diagnostics)) {
                return [];
            }

            return [
                CodeAction::fromArray([
                    'title' =>  $this->title,
                    'kind' => $this->kind(),
                    'diagnostics' => $diagnostics,
                    'command' => new Command(
                        $this->title,
                        TransformCommand::NAME,
                        [
                            $textDocument->uri,
                            $this->name
                        ]
                    )
                ])
            ];
        });
    }

    public function name(): string
    {
        return $this->name;
    }

    public function describe(): string
    {
        return sprintf('"%s" transformer', $this->name);
    }

    /**
     * @return Promise<array<Diagnostic>>
     */
    private function getDiagnostics(TextDocumentItem $textDocument): Promise
    {
        return call(function () use ($textDocument) {
            $phpactorTextDocument = TextDocumentConverter::fromLspTextItem($textDocument);

            return array_map(function (Diagnostic $diagnostic) {
                $diagnostic->message = sprintf('%s (fix with "%s" code action)', $diagnostic->message, $this->title);
                return $diagnostic;
            }, DiagnosticsConverter::toLspDiagnostics(
                $phpactorTextDocument,
                yield $this->transformers->get($this->name)->diagnostics(
                    SourceCode::fromTextDocument(TextDocumentConverter::fromLspTextItem($textDocument))
                )
            ));
        });
    }

    private function kind(): string
    {
        return 'quickfix.'.$this->name;
    }
}
