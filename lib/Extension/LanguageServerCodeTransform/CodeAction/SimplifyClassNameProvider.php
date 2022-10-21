<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\CodeTransform\Domain\Refactor\SimplifyClassName;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\SimplifyClassNameCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use function Amp\call;

class SimplifyClassNameProvider implements CodeActionProvider
{
    public const KIND = 'refactor.class.simplify';

    private SimplifyClassName $simplifyClassName;

    public function __construct(SimplifyClassName $simplifyClassName)
    {
        $this->simplifyClassName = $simplifyClassName;
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
            if (!$this->simplifyClassName->canSimplifyClassName(
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
                        SimplifyClassNameCommand::NAME,
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
