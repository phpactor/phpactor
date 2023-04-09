<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Amp\Success;
use Phpactor\CodeTransform\Domain\Refactor\FillObject;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;

class FillObjectProvider implements CodeActionProvider
{
    const KIND = 'quickfix.fill.object';

    public function __construct(private FillObject $fillObject)
    {
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        $edits = $this->fillObject->fillObject(
            TextDocumentConverter::fromLspTextItem($textDocument),
            RangeConverter::toPhpactorRange($range, $textDocument->text)->start()
        );

        if (count($edits) === 0) {
            return new Success([]);
        }

        return new Success([
            new CodeAction(
                title: 'Fill object',
                kind: self::KIND,
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
        return [self::KIND];
    }
    public function describe(): string
    {
        return 'fill new object construct with named parameters';
    }
}
