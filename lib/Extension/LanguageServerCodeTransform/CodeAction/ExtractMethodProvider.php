<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\Promise;
use Phpactor\CodeTransform\Domain\Refactor\ExtractMethod;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ExtractMethodCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use function Amp\call;

class ExtractMethodProvider implements CodeActionProvider
{
    public const KIND = 'refactor.extract.method';

    private ExtractMethod $extractMethod;

    public function __construct(ExtractMethod $extractMethod)
    {
        $this->extractMethod = $extractMethod;
    }

    
    public function kinds(): array
    {
        return [
            self::KIND
        ];
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range): Promise
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
                    'kind' => self::KIND,
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
