<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\CodeAction;

use Amp\Promise;
use Phpactor\CodeTransform\Domain\NameWithByteOffset;
use Phpactor\Extension\LanguageServerBridge\Converter\PositionConverter;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ImportAllUnresolvedNamesCommand;
use Phpactor\Extension\LanguageServerCodeTransform\LspCommand\ImportNameCommand;
use Phpactor\Extension\LanguageServerCodeTransform\Model\NameImport\CandidateFinder;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use function Amp\call;
use function Amp\delay;

class ImportNameProvider implements CodeActionProvider, DiagnosticsProvider
{
    /**
     * @var CandidateFinder
     */
    private $finder;

    /**
     * @var bool
     */
    private $reportNonExistingClasses;

    public function __construct(CandidateFinder $finder, bool $reportNonExistingClasses = true)
    {
        $this->finder = $finder;
        $this->reportNonExistingClasses = $reportNonExistingClasses;
    }

    public function provideActionsFor(TextDocumentItem $item, Range $range): Promise
    {
        return call(function () use ($item) {
            $actions = [];
            foreach ($this->finder->importCandidates($item) as $candidate) {
                $actions[] = $this->codeActionForFqn($candidate->unresolvedName(), $candidate->candidateFqn(), $item);
                yield delay(1);
            }

            if (count($actions) > 1) {
                $actions[] = $this->addImportAllAction($item);
            }

            return $actions;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function kinds(): array
    {
        return [
            'quickfix.import_class'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function provideDiagnostics(TextDocumentItem $textDocument): Promise
    {
        return call(function () use ($textDocument) {
            $diagnostics = [];
            $hasCandidatesHash = [];
            foreach ($this->finder->unresolved($textDocument) as $unresolvedName) {
                assert($unresolvedName instanceof NameWithByteOffset);
                $nameString = (string)$unresolvedName->name();
                [
                    $hasCandidates,
                    $diagnostic
                ] = $this->diagnosticsFromUnresolvedName(
                    $unresolvedName,
                    $textDocument,
                    isset($hasCandidatesHash[$nameString]) ? $hasCandidatesHash[$nameString] : null
                );
                $hasCandidatesHash[$nameString] = $hasCandidates;
                if ($diagnostic !== null) {
                    $diagnostics[] = $diagnostic;
                }
            }

            return $diagnostics;
        });
    }

    private function diagnosticsFromUnresolvedName(NameWithByteOffset $unresolvedName, TextDocumentItem $item, ?bool $hasCandidates = null): array
    {
        $range = new Range(
            PositionConverter::byteOffsetToPosition($unresolvedName->byteOffset(), $item->text),
            PositionConverter::intByteOffsetToPosition(
                $unresolvedName->byteOffset()->toInt() + strlen($unresolvedName->name()->head()->__toString()),
                $item->text
            )
        );

        if (null === $hasCandidates) {
            $hasCandidates = $this->finder->candidatesForUnresolvedName($unresolvedName)->current() !== null;
        }

        if (false === $hasCandidates) {
            if ($this->reportNonExistingClasses === false) {
                return [false, null];
            }
            return [
                false,
                new Diagnostic(
                    $range,
                    sprintf(
                        '%s "%s" does not exist',
                        ucfirst($unresolvedName->type()),
                        $unresolvedName->name()->head()->__toString()
                    ),
                    DiagnosticSeverity::ERROR,
                    null,
                    'phpactor'
                )
            ];
        }

        return [
            true,
            new Diagnostic(
                $range,
                sprintf(
                    '%s "%s" has not been imported',
                    ucfirst($unresolvedName->type()),
                    $unresolvedName->name()->head()->__toString()
                ),
                DiagnosticSeverity::HINT,
                null,
                'phpactor'
            )
        ];
    }

    private function codeActionForFqn(NameWithByteOffset $unresolvedName, string $fqn, TextDocumentItem $item): CodeAction
    {
        $diagnostics = $this->diagnosticsFromUnresolvedName($unresolvedName, $item, true);
        return CodeAction::fromArray([
            'title' => sprintf(
                'Import %s "%s"',
                $unresolvedName->type(),
                $fqn
            ),
            'kind' => 'quickfix.import_class',
            'isPreferred' => false,
            'diagnostics' => ($diagnostics[1] !== null) ? [$diagnostics[1]] : null,
            'command' => new Command(
                'Import name',
                ImportNameCommand::NAME,
                [
                    $item->uri,
                    $unresolvedName->byteOffset()->toInt(),
                    $unresolvedName->type(),
                    $fqn
                ]
            )
        ]);
    }

    private function addImportAllAction(TextDocumentItem $item): CodeAction
    {
        return CodeAction::fromArray([
            'title' => sprintf(
                'Import all unresolved names',
            ),
            'kind' => 'quickfix.import_all_unresolved_names',
            'isPreferred' => true,
            'diagnostics' => [],
            'command' => new Command(
                'Import all unresolved names',
                ImportAllUnresolvedNamesCommand::NAME,
                [
                    $item->uri,
                ]
            )
        ]);
    }
}
