<?php

namespace Phpactor\Extension\PhpCodeSniffer\Provider;

use Amp\CancellationToken;
use Amp\Promise;
use Amp\Success;
use Phpactor\Diff\RangesForDiff;
use Phpactor\Extension\PhpCodeSniffer\Model\PhpCodeSnifferProcess;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Psr\Log\LoggerInterface;
use SebastianBergmann\Diff\Parser;
use ArrayIterator;

class PhpCodeSnifferDiagnosticsProvider implements DiagnosticsProvider, CodeActionProvider
{

    public function __construct(
        private PhpCodeSnifferProcess $phpCodeSniffer,
        private bool $showDiagnostics,
        private RangesForDiff $rangeForDiff,
        private LoggerInterface $logger,
    ) {
    }

    /**
       * @return Promise<Diagnostic[]>
       */
    public function provideDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        if (!$this->showDiagnostics) {
            return new Success([]);
        }

        return \Amp\call(function () use ($textDocument, $cancel) {
            $diagnostics = yield $this->findDiagnostics($textDocument, $cancel);

            return $diagnostics ?: [];
        });
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return \Amp\call(function () use ($textDocument, $cancel) {
            $isFixable = yield $this->hasFixableDiagnostics($textDocument);
            if ($isFixable === false) {
                return [];
            }

            $diagnostics = yield $this->findDiagnostics($textDocument, $cancel);

            if ($diagnostics === false) {
                return [];
            }

            $title = 'Format with PHP Code Sniffer';

            $actions = [
              CodeAction::fromArray([
                'title' => $title,
                'kind' => 'source.fixAll.phpactor.phpCodeSniffer',
                'diagnostics' => $diagnostics,
                'command' => new Command(
                    $title,
                    'php_code_sniffer.fix',
                    [
                    $textDocument->uri
            ]
                )
              ])
            ];

            return $actions;
        });
    }

    public function kinds(): array
    {
        return ['source.fixAll.phpactor.phpCodeSniffer'];
    }

    public function name(): string
    {
        return 'phpcs';
    }

    public function describe(): string
    {
        return 'phpcs';
    }

    /**
     * @return Promise<bool>
     */
    private function hasFixableDiagnostics(TextDocumentItem $textDocument): Promise
    {
        return \Amp\call(function () use ($textDocument) {
            $outputJson = yield $this->phpCodeSniffer->diagnose($textDocument, [ '-m' ]);

            $output = json_decode($outputJson, flags: JSON_THROW_ON_ERROR);

            return $output->totals->fixable > 0;
        });
    }

    /**
     * @return Promise<Diagnostic[]|false> False when there are no diagnostics available for file, array othwerwise
     *                                     Array containing diagnostics to show
     */
    private function findDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        return \Amp\call(function () use ($textDocument) {
            $outputJson = yield $this->phpCodeSniffer->diagnose($textDocument);

            $output = json_decode($outputJson, flags: JSON_THROW_ON_ERROR);

            if (empty($output->files)) {
                return false;
            }

            // phpcs return array indexed by file name,
            // but we only deal with one file, thus don't care about
            // actual key
            $files = new ArrayIterator($output->files);
            $rules = $files->current()->messages;

            $diagnostics = [];

            $diffParser = new Parser();

            foreach ($rules as $rule) {
                // We treat non-fixable rules as 1 char range.
                if ($rule->fixable === false) {
                    $range = new Range(
                        new Position(--$rule->line, $rule->column),
                        new Position(--$rule->line, ++$rule->column)
                    );
                    $diagnostics[] = yield $this->createRuleDiagnostics($rule, $range);
                    continue;
                }

                $sniffWithoutSuffix = $this->getSniffGroup($rule->source);
                if ($sniffWithoutSuffix === null) {
                    continue;
                }

                $fileDiffText = yield $this->phpCodeSniffer->produceFixesDiff($textDocument, [$sniffWithoutSuffix]);
                $fileDiff = $diffParser->parse($fileDiffText);

                // one file input is passed and one file expected
                if (count($fileDiff) !== 1) {
                    $this->logger->warning(
                        sprintf("Expected phpcs to provide 1 diff, got %s. Skipping diagnostics for file '%s'", count($fileDiff), $textDocument->uri)
                    );

                    continue;
                }

                $ranges = $this->rangeForDiff->createRangesForDiff($fileDiff[0]);

                foreach ($ranges as $range) {
                    $diagnostics[] = yield $this->createRuleDiagnostics($rule, $range);
                }
            }

            return $diagnostics;
        });
    }

    /**
     * @return Promise<Diagnostic>
     */
    private function createRuleDiagnostics(object $rule, Range $range): Promise
    {
        return \Amp\call(function () use ($rule, $range) {
            return Diagnostic::fromArray([
              'message' => $rule->message,
              'range' => $range,
              'severity' => DiagnosticSeverity::WARNING,
              'source' => $this->name(),
              'code' => $rule->source
            ]);
        });
    }

    /**
     * When trying to apply a fix, we need to know the name of the sniff
     * group, not the exact sniff name.
     *
     * @return string|null Sniff with stripped last identifier.
     */
    private function getSniffGroup(string $source): ?string
    {
        preg_match("/(.*)\.\w+/", $source, $matches);
        if (! isset($matches[1])) {
            return null;
        }
        $sniffWithoutSuffix = $matches[1];
        return $sniffWithoutSuffix;
    }
}
