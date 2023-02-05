<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use LanguageServerProtocol\CodeActionKind;
use Phpactor\CodeTransform\Domain\Refactor\ReplaceQualifierWithImport;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ReplaceQualifierWithImportCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use function Amp\call;

class ReplaceQualifierWithImportProvider implements CodeActionProvider
{
    public const KIND = 'refactor.class.simplify';

    public function __construct(private ReplaceQualifierWithImport $replaceQualifierWithImport)
    {
    }

    public function kinds(): array
    {
        return [
            self::KIND,
            CodeActionKind::REFACTOR_REWRITE,
        ];
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument, $range) {
            if (!$this->replaceQualifierWithImport->canReplaceWithImport(
                SourceCode::fromStringAndPath($textDocument->text, $textDocument->uri),
                PositionConverter::positionToByteOffset($range->start, $textDocument->text)->toInt(),
            )) {
                return [];
            }

            return [
                CodeAction::fromArray([
                    'title' => 'Replace qualifier with import',
                    'kind' => self::KIND,
                    'diagnostics' => [],
                    'command' => new Command(
                        'Replace qualifier with import',
                        ReplaceQualifierWithImportCommand::NAME,
                        [
                            $textDocument->uri,
                            PositionConverter::positionToByteOffset($range->start, $textDocument->text)->toInt(),
                        ]
                    )
                ])
            ];
        });
    }
}
