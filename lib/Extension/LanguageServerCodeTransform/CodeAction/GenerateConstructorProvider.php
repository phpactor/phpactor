<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Amp\Success;
use Phpactor\CodeTransform\Domain\Refactor\GenerateConstructor;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\WorkspaceEditConverter;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\CodeActionKind;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;

class GenerateConstructorProvider implements CodeActionProvider
{
    const KIND = CodeActionKind::REFACTOR;

    public function __construct(
        private readonly GenerateConstructor $generateConstructor,
        private readonly WorkspaceEditConverter $converter
    ) {
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        $edits = $this->generateConstructor->generateMethod(
            TextDocumentConverter::fromLspTextItem($textDocument),
            RangeConverter::toPhpactorRange($range, $textDocument->text)->start()
        );

        if (count($edits) === 0) {
            return new Success([]);
        }

        return new Success([
            new CodeAction(
                title: 'Generate constructor',
                kind: self::KIND,
                diagnostics: [],
                isPreferred: false,
                edit: $this->converter->toLspWorkspaceEdit($edits)
            )
        ]);
    }

    public function kinds(): array
    {
        return [self::KIND];
    }

    public function describe(): string
    {
        return 'generate constructor for new object instantiation';
    }
}
