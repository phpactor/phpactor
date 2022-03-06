<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\Promise;
use Amp\Success;
use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\GenerateMethodCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\TextDocument\TextDocumentBuilder;
use function Amp\call;

class GenerateMethodProvider implements DiagnosticsProvider, CodeActionProvider
{
    public const KIND = 'quickfix.generate_method';

    /**
     * @var MissingMethodFinder
     */
    private $missingMethodFinder;

    public function __construct(MissingMethodFinder $missingMethodFinder)
    {
        $this->missingMethodFinder = $missingMethodFinder;
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
    /**
     * {@inheritDoc}
     */
    public function provideActionsFor(TextDocumentItem $textDocument, Range $range): Promise
    {
        return call(function () use ($textDocument) {
            $diagnostics = $this->getDiagnostics($textDocument);

            return array_map(function (Diagnostic $diagnostic) use ($textDocument) {
                return CodeAction::fromArray([
                    'title' => sprintf('Fix "%s"', $diagnostic->message),
                    'kind' => self::KIND,
                    'diagnostics' => [
                        $diagnostic
                    ],
                    'command' => new Command(
                        'Generate method',
                        GenerateMethodCommand::NAME,
                        [
                            $textDocument->uri,
                            PositionConverter::positionToByteOffset(
                                $diagnostic->range->start,
                                $textDocument->text
                            )->toInt()
                        ]
                    )
                ]);
            }, $diagnostics);
        });
    }
    /**
     * @return array<Diagnostic>
     */
    private function getDiagnostics(TextDocumentItem $textDocument): array
    {
        $methods = $this->missingMethodFinder->find(
            TextDocumentBuilder::create($textDocument->text)->build()
        );
        $diagnostics = [];

        foreach ($methods as $method) {
            $diagnostics[] = Diagnostic::fromArray([
                'range' => RangeConverter::toLspRange($method->range(), $textDocument->text),
                'message' => sprintf('Method "%s" does not exist', $method->name()),
                'severity' => DiagnosticSeverity::WARNING,
                'source' => 'phpactor',
            ]);
        }

        usort($diagnostics, function (Diagnostic $a, Diagnostic $b) {
            if ($a->range->start->line > $b->range->start->line) {
                return 1;
            }
            
            if ($a->range->start->line < $b->range->start->line) {
                return -1;
            }
            
            if ($a->range->start->character > $b->range->start->character) {
                return 1;
            }

            if ($a->range->start->character < $b->range->start->character) {
                return -1;
            }
            
            return 0;
        });

        return $diagnostics;
    }
}
