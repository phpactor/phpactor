<?php

namespace Phpactor\Extension\PhpCodeSniffer\Provider;

use function Amp\call;
use Amp\CancellationToken;
use Amp\Promise;
use Amp\Success;
use JsonException;
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
use RuntimeException;
use SebastianBergmann\Diff\Parser;
use ArrayIterator;

/**
 * @phpstan-type PhpcsResult object{
 *     files: array<string, PhpcsFileResult>,
 *     totals: object{
 *         errors: int<0, max>,
 *         warnings: int<0, max>,
 *         fixable: int<0, max>
 *     }
 * }
 *
 * @phpstan-type PhpcsFileResult object{
 *     errors: int<0, max>,
 *     warnings: int<0, max>,
 *     messages: PhpcsRule[]
 * }
 *
 * @phpstan-type PhpcsRule object{
 *     message: string,
 *     source: string,
 *     severity: int,
 *     fixable: bool,
 *     type: string,
 *     line: int,
 *     column: int
 * }
 */
class PhpCodeSnifferDiagnosticsProvider implements DiagnosticsProvider, CodeActionProvider
{

    public function __construct(
        private readonly PhpCodeSnifferProcess $phpCodeSniffer,
        private readonly bool $showDiagnostics,
        private readonly RangesForDiff $rangeForDiff,
        private readonly LoggerInterface $logger,
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

        return call(function () use ($textDocument, $cancel) {
            $diagnostics = yield $this->findDiagnostics($textDocument, $cancel);

            return $diagnostics ?: [];
        });
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument, $cancel) {
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
        return call(function () use ($textDocument) {
            $outputJson = yield $this->phpCodeSniffer->diagnose($textDocument, [ '-m' ]);

            try {
                /** @var PhpcsResult $output */
                $output = json_decode($outputJson, flags: JSON_THROW_ON_ERROR);
            } catch (JsonException $error) {
                throw new RuntimeException(sprintf(
                    'Could not decode JSON: %s',
                    $outputJson
                ));
            }

            return $output->totals->fixable > 0;
        });
    }

    /**
     * @return Promise<Diagnostic[]|false> False when there are no diagnostics available for file, array othwerwise
     *                                     Array containing diagnostics to show
     */
    private function findDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument) {
            $outputJson = yield $this->phpCodeSniffer->diagnose($textDocument);

            try {
                /** @var PhpcsResult $output */
                $output = json_decode($outputJson, flags: JSON_THROW_ON_ERROR);
            } catch (JsonException $error) {
                throw new RuntimeException(sprintf(
                    'Could not decode JSON: %s',
                    $outputJson
                ));
            }

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
                    $lineNo = $rule->line - 1;
                    $range = new Range(
                        new Position($lineNo, $rule->column),
                        new Position($lineNo, $rule->column + 1)
                    );
                    $diagnostics[] = $this->createRuleDiagnostics($rule, $range);
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
                    $diagnostics[] = $this->createRuleDiagnostics($rule, $range);
                }
            }

            return $diagnostics;
        });
    }

    /**
     * @param PhpcsRule $rule
     */
    private function createRuleDiagnostics(object $rule, Range $range): Diagnostic
    {
        return new Diagnostic(
            message: $rule->message,
            range: $range,
            severity: DiagnosticSeverity::WARNING,
            source: $this->name(),
            code: $rule->source
        );
    }

    /**
     * When trying to apply a fix, we need to know the name of the sniff
     * group, not the exact sniff name.
     *
     * @return string|null Sniff with stripped last identifier.
     */
    private function getSniffGroup(string $source): ?string
    {
        $matches = [];
        preg_match("/(.*)\.\w+/", $source, $matches);
        if (! isset($matches[1])) {
            return null;
        }
        $sniffWithoutSuffix = $matches[1];
        return $sniffWithoutSuffix;
    }
}
