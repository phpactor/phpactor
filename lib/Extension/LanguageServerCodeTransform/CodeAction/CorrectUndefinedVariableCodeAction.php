<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\CodeActionKind;
use Phpactor\LanguageServerProtocol\OptionalVersionedTextDocumentIdentifier;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentEdit;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServerProtocol\TextEdit;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UndefinedVariableDiagnostic;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use function Amp\call;

class CorrectUndefinedVariableCodeAction implements CodeActionProvider
{
    public const KIND = 'quickfix.correct_variable_name';

    public function __construct(private readonly SourceCodeReflector $reflector)
    {
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument, $cancel) {
            $actions = [];
            foreach ((yield $this->reflector->diagnostics(
                TextDocumentConverter::fromLspTextItem($textDocument)
            ))->byClass(
                UndefinedVariableDiagnostic::class
            ) as $diagnostic) {
                assert($diagnostic instanceof UndefinedVariableDiagnostic);
                foreach ($diagnostic->suggestions() as $suggestion) {
                    if ($cancel->isRequested()) {
                        return $actions;
                    }
                    $actions[] =  new CodeAction(
                        title: sprintf('Correct undefined variable "$%s" to "$%s"', $diagnostic->undefinedVariableName(), $suggestion),
                        kind: CodeActionKind::QUICK_FIX,
                        diagnostics: null,
                        isPreferred: null,
                        disabled: null,
                        edit: new WorkspaceEdit(
                            documentChanges: [
                                new TextDocumentEdit(
                                    new OptionalVersionedTextDocumentIdentifier($textDocument->uri, $textDocument->version),
                                    [
                                        new TextEdit(
                                            range: RangeConverter::toLspRange($diagnostic->range(), $textDocument->text),
                                            newText: '$' . $suggestion
                                        ),
                                    ]
                                )
                            ],
                        ),
                        command: null,
                    );
                }
            }
            return $actions;
        });
    }

    public function kinds(): array
    {
        return [
            self::KIND
        ];
    }

    public function describe(): string
    {
        return 'correct undefined variable name';
    }
}
