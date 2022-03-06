<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\Promise;
use Amp\Success;
use Microsoft\PhpParser\Parser;
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

    /**
     * @var Generators
     */
    private $generators;

    /**
     * @var Parser
     */
    private $parser;

    public function __construct(Generators $generators, Parser $parser)
    {
        $this->generators = $generators;
        $this->parser = $parser;
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
    public function provideDiagnostics(TextDocumentItem $textDocument): Promise
    {
        return new Success($this->getDiagnostics($textDocument));
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range): Promise
    {
        return call(function () use ($textDocument) {
            $diagnostics = $this->getDiagnostics($textDocument);

            if (empty($diagnostics)) {
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
                new Range(
                    new Position(1, 1),
                    new Position(1, 1)
                ),
                sprintf(
                    'Empty file (use create-class code action to create a new class)',
                ),
                DiagnosticSeverity::INFORMATION,
                null,
                'phpactor'
            )
        ];
    }

    private function kind(): string
    {
        return self::KIND;
    }
}
