<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\CodeTransform\Domain\Refactor\ExpandClass;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ExpandClassCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use function Amp\call;

class ExpandClassProvider implements CodeActionProvider
{
    public const KIND = 'refactor.class.expand';

    private ExpandClass $expandClass;

    public function __construct(ExpandClass $expandClass)
    {
        $this->expandClass = $expandClass;
    }


    public function kinds(): array
    {
        return [
            self::KIND
        ];
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument, $range) {
            if (!$this->expandClass->canExpandClassName(
                SourceCode::fromStringAndPath($textDocument->text, $textDocument->uri),
                PositionConverter::positionToByteOffset($range->start, $textDocument->text)->toInt(),
            )) {
                return [];
            }

            return [
                CodeAction::fromArray([
                    'title' => 'Expand class name',
                    'kind' => self::KIND,
                    'diagnostics' => [],
                    'command' => new Command(
                        'Expand class',
                        ExpandClassCommand::NAME,
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
