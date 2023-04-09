<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\CodeTransform\Domain\Refactor\ExtractConstant;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ExtractConstantCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use function Amp\call;

class ExtractConstantProvider implements CodeActionProvider
{
    public const KIND = 'refactor.extract.constant';

    public function __construct(private ExtractConstant $extractConstant)
    {
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
            if (!$this->extractConstant->canExtractConstant(
                SourceCode::fromStringAndPath($textDocument->text, $textDocument->uri),
                PositionConverter::positionToByteOffset($range->start, $textDocument->text)->toInt(),
            )) {
                return [];
            }

            return [
                CodeAction::fromArray([
                    'title' =>  'Extract constant',
                    'kind' => self::KIND,
                    'diagnostics' => [],
                    'command' => new Command(
                        'Extract constant',
                        ExtractConstantCommand::NAME,
                        [
                            $textDocument->uri,
                            PositionConverter::positionToByteOffset($range->start, $textDocument->text)->toInt(),
                            PositionConverter::positionToByteOffset($range->end, $textDocument->text)->toInt()
                        ]
                    )
                ])
            ];
        });
    }
    public function describe(): string
    {
        return 'extract constant';
    }
}
