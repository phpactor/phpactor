<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\Promise;
use Phpactor\CodeTransform\Domain\Refactor\ExtractExpression;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ExtractExpressionCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use function Amp\call;

class ExtractExpressionProvider implements CodeActionProvider
{
    public const KIND = 'refactor.extract.expression';

    /**
     * @var ExtractExpression
     */
    private $extractExpression;

    public function __construct(ExtractExpression $extractExpression)
    {
        $this->extractExpression = $extractExpression;
    }
    /**
     * {@inheritDoc}
     */
    public function kinds(): array
    {
        return [
            self::KIND
        ];
    }
    /**
     * {@inheritDoc}
     */
    public function provideActionsFor(TextDocumentItem $textDocument, Range $range): Promise
    {
        return call(function () use ($textDocument, $range) {
            if (!$this->extractExpression->canExtractExpression(
                SourceCode::fromStringAndPath($textDocument->text, $textDocument->uri),
                PositionConverter::positionToByteOffset($range->start, $textDocument->text)->toInt(),
                PositionConverter::positionToByteOffset($range->end, $textDocument->text)->toInt()
            )) {
                return [];
            }

            return [
                CodeAction::fromArray([
                    'title' =>  'Extract expression',
                    'kind' => self::KIND,
                    'diagnostics' => [],
                    'command' => new Command(
                        'Extract method',
                        ExtractExpressionCommand::NAME,
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
