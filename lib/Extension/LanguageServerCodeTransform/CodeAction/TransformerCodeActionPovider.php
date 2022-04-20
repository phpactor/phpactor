<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\Promise;
use Amp\Success;
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
    private string $title;

    private Transformers $transformers;

    private string $name;

    public function __construct(Transformers $transformers, string $name, string $title)
    {
        $this->title = $title;
        $this->transformers = $transformers;
        $this->name = $name;
    }

    
    public function kinds(): array
    {
        return [
            $this->kind()
        ];
    }

    
    public function provideDiagnostics(TextDocumentItem $textDocument): Promise
    {
        return new Success($this->getDiagnostics($textDocument));
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range): Promise
    {
        return call(function () use ($textDocument) {
            $diagnostics = $this->getDiagnostics($textDocument);
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

    /**
     * @return array<Diagnostic>
     */
    private function getDiagnostics(TextDocumentItem $textDocument): array
    {
        $phpactorTextDocument = TextDocumentConverter::fromLspTextItem($textDocument);

        return array_map(function (Diagnostic $diagnostic) {
            $diagnostic->message = sprintf('%s (fix with "%s" code action)', $diagnostic->message, $this->title);
            return $diagnostic;
        }, DiagnosticsConverter::toLspDiagnostics(
            $phpactorTextDocument,
            $this->transformers->get($this->name)->diagnostics(
                SourceCode::fromStringAndPath($textDocument->text, $phpactorTextDocument->uri()->path())
            )
        ));
    }

    private function kind(): string
    {
        return 'quickfix.'.$this->name;
    }
}
