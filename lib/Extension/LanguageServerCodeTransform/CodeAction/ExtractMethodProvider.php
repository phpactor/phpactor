<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\CodeTransform\Domain\Refactor\ExtractMethod;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ExtractMethodCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\CodeActionKind;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use function Amp\call;

class ExtractMethodProvider implements CodeActionProvider
{
    public const KIND = 'refactor.extract.method';

    public function __construct(private ExtractMethod $extractMethod)
    {
    }


    public function kinds(): array
    {
        return [
            CodeActionKind::REFACTOR_EXTRACT,
            self::KIND
        ];
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument, $range) {
            if (!$this->extractMethod->canExtractMethod(
                SourceCode::fromStringAndPath($textDocument->text, $textDocument->uri),
                PositionConverter::positionToByteOffset($range->start, $textDocument->text)->toInt(),
                PositionConverter::positionToByteOffset($range->end, $textDocument->text)->toInt()
            )) {
                return [];
            }

            return [
                CodeAction::fromArray([
                    'title' =>  'Extract method',
                    'kind' => $this->kinds()[0],
                    'diagnostics' => [],
                    'command' => new Command(
                        'Extract method',
                        ExtractMethodCommand::NAME,
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
}
