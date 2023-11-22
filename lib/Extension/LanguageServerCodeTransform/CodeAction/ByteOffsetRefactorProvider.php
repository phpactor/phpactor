<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Amp\Success;
use Phpactor\CodeTransform\Domain\Refactor\ByteOffsetRefactor;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;

class ByteOffsetRefactorProvider implements CodeActionProvider
{
    public function __construct(
        private ByteOffsetRefactor $fillObject,
        private string $kind,
        private string $title,
        private string $description,
    ) {
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        $edits = $this->fillObject->refactor(
            TextDocumentConverter::fromLspTextItem($textDocument),
            RangeConverter::toPhpactorRange($range, $textDocument->text)->start()
        );

        if (count($edits) === 0) {
            return new Success([]);
        }

        return new Success([
            new CodeAction(
                title: $this->title,
                kind: $this->kind,
                diagnostics: [],
                isPreferred: false,
                edit: new WorkspaceEdit([
                    $textDocument->uri => TextEditConverter::toLspTextEdits($edits, $textDocument->text)
                ])
            )
        ]);
    }

    public function kinds(): array
    {
        return [$this->kind];
    }
    public function describe(): string
    {
        return $this->description;
    }
}
