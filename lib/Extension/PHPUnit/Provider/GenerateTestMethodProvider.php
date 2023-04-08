<?php

namespace Phpactor\Extension\PHPUnit\Provider;

use Amp\CancellationToken;
use Amp\Promise;
use Amp\Success;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\PHPUnit\CodeTransform\GenerateTestMethods;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\CodeActionKind;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\Extension\PHPUnit\LspCommand\GenerateTestMethodCommand;

class GenerateTestMethodProvider implements CodeActionProvider
{
    public function __construct(private GenerateTestMethods $generateTestMethods)
    {
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        $methodsThatCanBeGenerated = $this->generateTestMethods->getGeneratableTestMethods(
            SourceCode::fromStringAndPath($textDocument->text, $textDocument->uri)
        );

        $availableCodeActions = [];
        foreach ($methodsThatCanBeGenerated as $methodName) {
            $availableCodeActions[] = new CodeAction(
                title: 'Generate method ' . $methodName,
                kind: $this->kinds()[0],
                diagnostics: [],
                isPreferred: false,
                command: new Command(
                    title: 'Test Methods',
                    command: GenerateTestMethodCommand::NAME,
                    arguments: [
                        $textDocument->uri,
                        $methodName,
                    ]
                )
            );
        }

        return new Success($availableCodeActions);
    }

    public function kinds(): array
    {
        return [
            CodeActionKind::REFACTOR
        ];
    }
}
