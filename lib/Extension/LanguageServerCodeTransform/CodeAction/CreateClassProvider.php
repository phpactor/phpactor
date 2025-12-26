<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Amp\Success;
use Phpactor\CodeTransform\Domain\Generators;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\CreateClassCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use function Amp\call;

class CreateClassProvider implements DiagnosticsProvider, CodeActionProvider
{
    public const KIND = 'quickfix.create_class';

    public function __construct(private readonly Generators $generators)
    {
    }


    public function kinds(): array
    {
        return [
            self::KIND
        ];
    }


    public function provideDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        return new Success($this->getDiagnostics($textDocument));
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument) {
            $diagnostics = $this->getDiagnostics($textDocument);

            if ($diagnostics === []) {
                return [];
            }

            $actions = [];

            foreach ($this->generators as $name => $generator) {
                $title = sprintf('Create new "%s" class', $name);
                $actions[] = CodeAction::fromArray([
                    'title' =>  $title,
                    'kind' => self::KIND,
                    'diagnostics' => $diagnostics,
                    'command' => new Command(
                        $title,
                        CreateClassCommand::NAME,
                        [
                            $textDocument->uri,
                            $name
                        ]
                    )
                ]);
            }

            return $actions;
        });
    }

    public function name(): string
    {
        return 'create-class';
    }

    public function describe(): string
    {
        return 'create class in empty file';
    }

    /**
     * @return array<Diagnostic>
     */
    private function getDiagnostics(TextDocumentItem $textDocument): array
    {
        if ('' !== trim($textDocument->text)) {
            return [];
        }

        return [
            new Diagnostic(
                range: new Range(
                    new Position(1, 1),
                    new Position(1, 1)
                ),
                message: sprintf(
                    'Empty file (use create-class code action to create a new class)',
                ),
                severity: DiagnosticSeverity::INFORMATION,
                source: 'phpactor'
            )
        ];
    }
}
