<?php

namespace Phpactor\Diff;

use Phpactor\LanguageServerProtocol\Range;
use Phpactor\LanguageServerProtocol\Position;
use Phpactor\LanguageServerProtocol\TextEdit;
use SebastianBergmann\Diff\Line;
use SebastianBergmann\Diff\Parser;

class DiffToTextEditsConverter
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * @return TextEdit[]
     */
    public function toTextEdits(string $diffText): array
    {
        $parsedDiffs = $this->parser->parse($diffText);

        $edits = [];

        foreach ($parsedDiffs as $diff) {
            foreach ($diff->getChunks() as $chunk) {
                $consumer = new DiffLinesConsumer($chunk);

                while ($consumer->current()) {
                    if ($consumer->eatUnchanged()) {
                        continue;
                    }

                    $startLine = $consumer->getOrigLine() - 1;

                    if (($added = $consumer->eatAdded()) !== null) {
                        $edits[] = new TextEdit(
                            new Range(
                                new Position($startLine, 0),
                                new Position($startLine, 0)
                            ),
                            $this->linesToString($added)
                        );
                    };

                    if (($removed = $consumer->eatRemoved()) !== null) {
                        $added = $consumer->eatAdded();

                        $edits[] = new TextEdit(
                            new Range(
                                new Position($startLine, 0),
                                new Position($startLine + count($removed), 0)
                            ),
                            $this->linesToString($added)
                        );
                    }
                }
            }
        }

        return $edits;
    }

    /**
     * @param  Line[]|null  $lines
     */
    private function linesToString(?array $lines): string
    {
        if ($lines === null || count($lines) === 0) {
            return '';
        }

        return join("\n", array_map(fn (Line $line) => $line->getContent(), $lines))."\n";
    }
}
