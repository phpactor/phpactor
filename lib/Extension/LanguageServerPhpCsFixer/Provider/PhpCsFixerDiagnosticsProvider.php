<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Provider;

use function Amp\call;
use Amp\CancellationToken;
use Amp\Promise;
use Amp\Success;
use Phpactor\Diff\RangesForDiff;
use Phpactor\Extension\LanguageServerPhpCsFixer\Model\PhpCsFixerProcess;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\LanguageServerProtocol\CodeAction;
use Phpactor\LanguageServerProtocol\Command;
use Phpactor\LanguageServerProtocol\Diagnostic;
use Phpactor\LanguageServerProtocol\DiagnosticSeverity;
use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Psr\Log\LoggerInterface;
use SebastianBergmann\Diff\Parser;

class PhpCsFixerDiagnosticsProvider implements DiagnosticsProvider, CodeActionProvider
{
    /** @var array<string, string> */
    private array $ruleDescriptions = [];

    public function __construct(
        private readonly PhpCsFixerProcess $phpCsFixer,
        private readonly RangesForDiff $rangeForDiff,
        private readonly bool $showDiagnostics,
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
            $diagnostics = yield $this->findDiagnostics($textDocument, $cancel);

            if (false === $diagnostics) {
                return [];
            }

            $title = 'Format with PHP CS Fixer';

            return [
                CodeAction::fromArray([
                    'title' => $title,
                    'kind' => 'source.fixAll.phpactor.phpCsFixer',
                    'diagnostics' => $diagnostics,
                    'command' => new Command(
                        $title,
                        'php_cs_fixer.fix',
                        [
                            $textDocument->uri,
                        ]
                    ),
                ]),
            ];
        });
    }

    public function kinds(): array
    {
        return ['source.fixAll.phpactor.phpCsFixer'];
    }

    public function name(): string
    {
        return 'php-cs-fixer';
    }

    public function describe(): string
    {
        return 'php-cs-fixer';
    }

    /**
     * @return Promise<Diagnostic[]|false> False when there are no diagnostics available for file, array othwerwise
     *                                     Array containing diagnostics to show
     */
    private function findDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        return call(function () use ($textDocument) {
            $outputJson = yield $this->phpCsFixer->fix($textDocument->text, [
                '--dry-run',
                '--verbose',
                '--format',
                'json',
            ]);

            $output = json_decode($outputJson, flags: JSON_THROW_ON_ERROR);

            if (empty($output->files)) {
                return false;
            }

            $rules = $output->files[0]->appliedFixers;

            $diagnostics = [];

            $diffParser = new Parser();

            foreach ($rules as $rule) {
                $fileDiffText = yield $this->phpCsFixer->fix($textDocument->text, ['--dry-run', '--diff', '--using-cache', 'no', '--rules', $rule]);
                $fileDiff = $diffParser->parse($fileDiffText);

                // one file input is passed and one file expected
                if (1 !== count($fileDiff)) {
                    $this->logger->warning(
                        sprintf("Expected php-cs-fixer to provide 1 diff, got %s. Skipping diagnostics for file '%s'", count($fileDiff), $textDocument->uri)
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
    private function createRuleDiagnostics(string $rule, Range $range): Promise
    {
        return call(function () use ($rule, $range) {
            return Diagnostic::fromArray([
                'message' => yield $this->explainRule($rule),
                'range' => $range,
                'severity' => DiagnosticSeverity::WARNING,
                'source' => $this->name(),
                'code' => $rule,
            ]);
        });
    }

    /**
     * @return Promise<string>
     */
    private function explainRule(string $rule): Promise
    {
        if (isset($this->ruleDescriptions[$rule])) {
            return new Success($this->ruleDescriptions[$rule]);
        }

        return call(function () use ($rule) {
            $description = yield $this->phpCsFixer->describe($rule);

            // @see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/src/Console/Command/DescribeCommand.php
            // for a class producing descriptions output in php-cs-fixer

            // preg_replace calls below are matching content generated from above class, not content of individual rules.

            // "clean up" the description
            $description = (string)preg_replace('/Description of ([\\w\\-\\_ ]+) rule.*/', '', $description);
            $description = (string)preg_replace('/Fixer is configurable using following option.*/s', '', $description);
            $description = (string)preg_replace('/Fixing examples:.*/s', '', $description);
            $description = (string)preg_replace('/Description of the `[^`]+` rule./s', '', $description);
            $description = trim($description);

            $this->ruleDescriptions[$rule] = $description;

            return $this->ruleDescriptions[$rule];
        });
    }
}
