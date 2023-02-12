<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Amp\Success;
use Phpactor\CodeTransform\Domain\Refactor\PromoteProperty;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;

class PromotePropertyProvider implements CodeActionProvider
{
    const KIND = 'quickfix.constructor.promote_property';

    public function __construct(private PromoteProperty $promoteProperty)
    {
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        $edits = $this->promoteProperty->promoteProperty(
            TextDocumentConverter::fromLspTextItem($textDocument),
            RangeConverter::toPhpactorRange($range, $textDocument->text)->start()
        );

        if (count($edits) === 0) {
            return new Success([]);
        }

        return new Success([
            new CodeAction(
                'Promote property',
                self::KIND,
                [],
                false,
                new WorkspaceEdit([
                    $textDocument->uri => TextEditConverter::toLspTextEdits($edits, $textDocument->text)
                ])
            )
        ]);
    }

    public function kinds(): array
    {
        return [self::KIND];
    }
}
