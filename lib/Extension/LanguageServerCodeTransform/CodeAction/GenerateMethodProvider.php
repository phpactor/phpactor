<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\CancellationToken;
use Amp\Promise;
use Phpactor\CodeTransform\Domain\Helper\MissingMethodFinder;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\RangeConverter;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\GenerateMethodCommand;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use function Amp\call;

class GenerateMethodProvider implements DiagnosticsProvider, CodeActionProvider
{
    public const KIND = 'quickfix.generate_method';

    public function __construct(private MissingMethodFinder $missingMethodFinder)
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
        return $this->getDiagnostics($textDocument);
    }


    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument) {
            $diagnostics = yield $this->getDiagnostics($textDocument);

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

    public function name(): string
    {
        return 'generate-method';
    }

    public function describe(): string
    {
        return 'generate non-existing method';
    }

    /**
     * @return Promise<array<Diagnostic>>
     */
    private function getDiagnostics(TextDocumentItem $textDocument): Promise
    {
        return call(function () use ($textDocument) {
            $methods = yield $this->missingMethodFinder->find(
                TextDocumentConverter::fromLspTextItem($textDocument)
            );
            $diagnostics = [];

            foreach ($methods as $method) {
                $diagnostics[] = new Diagnostic(
                    range: RangeConverter::toLspRange($method->range(), $textDocument->text),
                    message: sprintf('Method "%s" does not exist', $method->name()),
                    severity: DiagnosticSeverity::WARNING,
                    source: 'phpactor',
                );
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
        });
    }
}
