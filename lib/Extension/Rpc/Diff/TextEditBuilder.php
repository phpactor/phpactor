<?php

namespace Phpactor\Extension\Rpc\Diff;

use SebastianBergmann\Diff\Differ as BergmannDiffer;

/**
 * Build an array of text edits required to transform one source file to another.
 *
 * Text Edits are based on the LSP TextEdit object:
 *     https://github.com/Microsoft/language-server-protocol/blob/gh-pages/specification.md#textedit
 *
 * This is a weak implementation and uses a line-by-line diff alogrithm. All
 * character offsets are 0.
 */
class TextEditBuilder
{
    const NOOP = 0;
    const ADD = 1;
    const REMOVE = 2;

    private BergmannDiffer $differ;

    public function __construct(BergmannDiffer $differ = null)
    {
        $this->differ = $differ ?: new BergmannDiffer();
    }

    /**
     * @return array<int,array{start:array{line:int,character:int},end:array{line:int,character:int},text:string}>
     */
    public function calculateTextEdits(string $original, string $new): array
    {
        $edits = [];
        $diff = $this->differ->diffToArray($original, $new);
        $lineNumber = -1;

        $skipNext = false;
        foreach ($diff as $index => $line) {
            if ($skipNext) {
                $skipNext = false;
                continue;
            }

            $lineNumber++;
            $nextLine = $diff[$index + 1] ?? null;
            $token = $line[0];

            // nothing
            if ($line[1] === self::NOOP) {
                continue;
            }

            // replace
            if ($line[1] === self::REMOVE && $nextLine && $nextLine[1] === self::ADD) {
                $edits[] = [
                    'start' => [
                        'line' => $lineNumber,
                        'character' => 0,
                    ],
                    'end' => [
                        'line' => $lineNumber +  1,
                        'character' => 0,
                    ],
                    'text' => $nextLine[0],
                ];
                $skipNext = true;
                continue;
            }

            if ($line[1] === self::ADD) {
                $edits[] = [
                    'start' => [
                        'line' => $lineNumber,
                        'character' => 0,
                    ],
                    'end' => [
                        'line' => $lineNumber,
                        'character' => 0,
                    ],
                    'text' => $line[0],
                ];
                continue;
            }

            // remove
            if ($line[1] === 2) {
                $edits[] = [
                    'start' => [
                        'line' => $lineNumber,
                        'character' => 0,
                    ],
                    'end' => [
                        'line' => $lineNumber +  1,
                        'character' => 0,
                    ],
                    'text' => '',
                ];
                continue;
            }
        }

        return $edits;
    }
}
