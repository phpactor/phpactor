<?php

namespace Phpactor\Search\Adapter\Symfony;

use Phpactor\Extension\Core\Console\Formatter\Highlight;
use Phpactor\Search\Model\DocumentMatches;
use Phpactor\Search\Model\MatchRenderer;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\Search\Model\PatternMatch;
use Phpactor\TextDocument\LineCol;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\Util\LineAtOffset;
use Symfony\Component\Console\Output\OutputInterface;

final class ConsoleMatchRenderer implements MatchRenderer
{
    private OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function render(DocumentMatches $matches): void
    {
        $document = $matches->document();
        $this->output->write(sprintf('<fg=cyan>%s</>:', $matches->document()->uri()->path()));
        foreach ($matches as $match) {
            foreach ($match->tokens() as $token) {
                $startLineCol = LineCol::fromByteOffset($document, $match->range()->start());
                $endLineCol = LineCol::fromByteOffset($document, $match->range()->end());

                $this->output->writeln(str_replace("\n", ' ', sprintf(
                    '(%d:%d,%d:%d) %s',
                    $startLineCol->line(),
                    $startLineCol->col(),
                    $endLineCol->line(),
                    $endLineCol->col(),
                    $this->highlightAtCol(
                        LineAtOffset::lineAtByteOffset($document, ByteOffset::fromInt($match->range()->start()->toInt() + 1)),
                        substr($document->__toString(), $match->range()->start()->toInt(), $match->range()->length()),
                        $startLineCol->col() - 1
                    )
                )));
            }
        }
    }

    public function highlightAtCol(string $line, string $subject, int $col): string
    {
        $leftBracket = '<fg=red>';
        $rightBracket = '</>';

        return sprintf(
            '%s%s%s%s%s',
            substr($line, 0, $col),
            $leftBracket,
            $subject,
            $rightBracket,
            substr($line, $col + strlen($subject))
        );
    }
}
