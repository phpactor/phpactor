<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UndefinedVariableDiagnostic;
use Phpactor\WorseReflection\Core\Reflector\SourceCodeReflector;
use function Amp\call;

class FixUndefinedVariableCodeAction implements CodeActionProvider
{
    public function __construct(private SourceCodeReflector $reflector)
    {
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument, $range, $cancel) {
            foreach ((yield $this->reflector->diagnostics(
                TextDocumentConverter::fromLspTextItem($textDocument)
            ))->byClass(
                UndefinedVariableDiagnostic::class
            ) as $diagnostic) {
                dd($diagnostic);
            }
        });
    }

    public function kinds(): array
    {
    }

    public function describe(): string
    {
    }
}
