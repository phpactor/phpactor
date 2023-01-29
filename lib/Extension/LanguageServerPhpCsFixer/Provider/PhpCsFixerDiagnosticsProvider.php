<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Provider;

use Amp\CancellationToken;
use Amp\Promise;
use Amp\Success;
use Phpactor\Extension\LanguageServerPhpCsFixer\Model\PhpCsFixerProcess;
use Phpactor\Extension\LanguageServerPhpCsFixer\Util\StringSharedChars;
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
use SebastianBergmann\Diff\Line;
use SebastianBergmann\Diff\Parser;
use LogicException;

class PhpCsFixerDiagnosticsProvider implements DiagnosticsProvider, CodeActionProvider
{
    private array $ruleDescriptions = [];

    public function __construct(
        private PhpCsFixerProcess $phpCsFixer,
        private bool $showDiagnostics,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @return Promise<array|false> False when there are no diagnostics available for file, array with diagnostics to show
     */
    public function provideDiagnostics(TextDocumentItem $textDocument, CancellationToken $cancel): Promise
    {
        return \Amp\call(function () use ($textDocument) {
            $outputJson = yield $this->phpCsFixer->fix($textDocument->text, [
                '--dry-run',
                '--verbose',
                '--format',
                'json'
            ]);

            $output = json_decode($outputJson, false, JSON_THROW_ON_ERROR);

            if (empty($output->files)) {
                return false;
            }

            if (!$this->showDiagnostics) {
                return [];
            }

            $rules = $output->files[0]->appliedFixers;

            $diagnostics = [];

            $diffParser = new Parser();

            foreach ($rules as $rule) {
                $fileDiffText = yield $this->phpCsFixer->fix($textDocument->text, ['--dry-run', '--diff', '--rules', $rule]);
                $fileDiff = $diffParser->parse($fileDiffText);

                if (count($fileDiff) !== 1) {
                    $this->logger->warning(
                        sprintf("Expected php-cs-fixer to provide 1 diff, got %s. Skipping diagnostics for file '%s'", count($fileDiff), $textDocument->uri)
                    );

                    continue;
                }

                $fileDiff = $fileDiff[0];

                foreach ($fileDiff->getChunks() as $chunk) {
                    // diff is 1-indexed + in a line loop we update this number beforehand
                    $lineNo = $chunk->getStart() - 2;

                    $changedLines = [];
                    $replacedLines = null;
                    $startLineNo = null;

                    foreach ($chunk->getLines() as $index => $line) {
                        // increment orig file line number (added lines are not part of orig file)
                        if (in_array($line->getType(), [Line::UNCHANGED, Line::REMOVED])) {
                            $lineNo++;
                        }

                        $lastChangedLine = end($changedLines);

                        // consume same as previous line
                        if ($lastChangedLine && $line->getType() === $lastChangedLine->getType()) {
                            $changedLines[] = $line;
                            continue;
                        }

                        // consume lines if previous were removed and now we getting a replacement ones
                        if ($lastChangedLine && $lastChangedLine->getType() === Line::REMOVED && $line->getType() === Line::ADDED) {
                            $replacedLines = $changedLines;
                            $changedLines = [$line];

                            continue;
                        }

                        if (count($changedLines) > 0) {
                            $startPos = new Position($startLineNo, 0);
                            $lineLength = strlen($lastChangedLine->getContent());
                            $endPos = $lineLength
                                ? new Position($lineNo - 1, $lineLength)
                                : new Position($lineNo, 0);

                            if ($replacedLines) {
                                $firstLineA = $replacedLines[0]->getContent();
                                $firstLineB = $changedLines[0]->getContent();
                                $lastLineA = end($replacedLines)->getContent();
                                $lastLineB = end($changedLines)->getContent();

                                $startChars = StringSharedChars::startLength($firstLineA, $firstLineB);
                                $endChars = StringSharedChars::endPos($lastLineA, $lastLineB);

                                $startPos = new Position($startLineNo, $startChars);
                                $endPos = new Position($lineNo - 1, $endChars);
                            }

                            $diagnostics[] = Diagnostic::fromArray([
                                'message' => yield $this->explainRule($rule),
                                'range' => new Range($startPos, $endPos),
                                'severity' => DiagnosticSeverity::WARNING,
                                'source' => $this->name().':'.$rule
                            ]);

                            $startLineNo = null;
                            $changedLines = [];
                        }

                        if ($line->getType() === Line::UNCHANGED) {
                            continue;
                        }

                        if ($line->getType() === Line::REMOVED) {
                            $startLineNo = $lineNo;
                            $changedLines[] = $line;

                            continue;
                        }

                        // new line additions
                        if ($line->getType() === Line::ADDED && $chunk->getLines()[$index - 1]->getType() === Line::UNCHANGED) {
                            $diagnostics[] = Diagnostic::fromArray([
                                'message' => yield $this->explainRule($rule),
                                'range' => new Range(new Position($lineNo, 0), new Position($lineNo, 1)),
                                'severity' => DiagnosticSeverity::WARNING,
                                'source' => $this->name().':'.$rule
                            ]);

                            $contextLines = [];

                            continue;
                        }

                        throw new LogicException('This is not intended to be reached');
                    }
                }
            }

            return $diagnostics;
        });
    }

    public function provideActionsFor(TextDocumentItem $textDocument, Range $range, CancellationToken $cancel): Promise
    {
        return \Amp\call(function () use ($textDocument, $cancel) {
            $title = 'Format with PHP CS Fixer';

            $diagnostics = yield $this->provideDiagnostics($textDocument, $cancel);

            if ($diagnostics === false) {
                return [];
            }

            $actions = [
                CodeAction::fromArray([
                    'title' => $title,
                    'kind' => 'quickfix',
                    'diagnostics' => $diagnostics,
                    'command' => new Command(
                        $title,
                        'php_cs_fixer.fix',
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
        return ['quickfix'];
    }

    public function name(): string
    {
        return 'php-cs-fixer';
    }

    private function explainRule(string $rule): Promise
    {
        if (isset($this->ruleDescriptions[$rule])) {
            return new Success($this->ruleDescriptions[$rule]);
        }

        return \Amp\call(function () use ($rule) {
            $description = yield $this->phpCsFixer->describe($rule);

            // Look class linked below is respnsible for producing descriptions output
            // @see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/src/Console/Command/DescribeCommand.php

            // preg_replace calls below are matching content generated from above class, not content of individual rules.

            // remove a generic line
            $description = preg_replace("/Description of ([\w\-\_ ]+) rule.*/", '', $description);

            // remove configuration option descriptions, as that's not describing problem that's showing in code
            $description = preg_replace('/Fixer is configurable using following option.*/s', '', $description);

            // remove example diffs for the rule
            $description = preg_replace('/Fixing examples:.*/s', '', $description);

            $description = trim($description);

            $this->ruleDescriptions[$rule] = $description;

            return $this->ruleDescriptions[$rule];
        });
    }
}
