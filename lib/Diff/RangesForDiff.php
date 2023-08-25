<?php

namespace Phpactor\Diff;

use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\Range;
use SebastianBergmann\Diff\Line;
use SebastianBergmann\Diff\Diff;
use LogicException;

class RangesForDiff
{
    /**
     * Creates Ranges from Diff:
     *
     * - For replacements and removals, Range is a removed code
     * - For additions, Range is 1 char width range on the first line of addition
     *
     * @return Range[]
     */
    public function createRangesForDiff(Diff $fileDiff): array
    {
        $ranges = [];

        foreach ($fileDiff->getChunks() as $chunk) {
            // diff is 1-indexed + in a line loop we update this number beforehand
            $lineNo = $chunk->getStart() - 2;

            /** @var Line[] */
            $changedLines = [];
            /** @var Line[]|null */
            $replacedLines = null;
            /** @var int|null */
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

                if ($lastChangedLine) {
                    if (empty($changedLines) || !$startLineNo) {
                        throw new LogicException("Missing logic data that's expected to be set");
                    }

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

                    $ranges[] = new Range($startPos, $endPos);

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

                $prevLine = $chunk->getLines()[$index - 1];

                if ($prevLine->getContent() === "\ No newline at end of file") {
                    $contextLines = [];

                    continue;
                }

                if ($line->getType() === Line::ADDED
                  && $prevLine->getType() === Line::UNCHANGED
                ) {
                    $ranges[] = new Range(new Position($lineNo, 0), new Position($lineNo, 1));
                    $contextLines = [];

                    continue;
                }
            }
        }

        return $ranges;
    }
}
