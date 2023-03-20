<?php

namespace Phpactor\Extension\PHPUnit\Provider;

use Amp\CancellationToken;
use Amp\Promise;
use Amp\Success;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\WorkspaceEditConverter;
use Phpactor\Extension\PHPUnit\CodeTransform\GenerateTestMethods;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\CodeActionKind;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;

class GenerateTestMethodProvider implements CodeActionProvider
{
    public function __construct(
        private GenerateTestMethods $generateTestMethods,
        private WorkspaceEditConverter $converter
    ){}

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        $edits = $this->generateTestMethods->generateMethod(
            TextDocumentConverter::fromLspTextItem($textDocument),
            RangeConverter::toPhpactorRange($range, $textDocument->text)->start()
        );

        if (count($edits) === 0) {
            return new Success([]);
        }
        dd("hello");

        return new Success([
            new CodeAction(
                title: 'Test Methods',
                kind: $this->kinds()[0],
                diagnostics: [],
                isPreferred: false,
                edit: $this->converter->toLspWorkspaceEdit($edits)
            )
        ]);
    }


    public function kinds(): array
    {
        return [
            CodeActionKind::REFACTOR
        ];
    }

}
