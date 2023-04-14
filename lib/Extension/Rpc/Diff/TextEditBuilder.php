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
    private BergmannDiffer $differ;
    public function __construct(BergmannDiffer $differ = null)
    {
        $this->differ = $differ ?: new BergmannDiffer();
    }

    public function calculateTextEdits(string $original, string $new)
    {
        $edits = [];
        $diff = $this->differ->diffToArray($original, $new);
        $lineNumber = -1;

        foreach ($diff as $line) {
            $lineNumber++;
            $token = $line[0];
            switch ($line[1]) {
                // Nothing
                case 0:
                    break;

                    // Added
                case 1:
                    $edits[] = [
                        'start' => [
                            'line' => $lineNumber,
                            'character' => 0,
                        ],
                        'end' => [
                            'line' => $lineNumber,
                            'character' => 0,
                        ],
                        'text' => $token,
                    ];
                    break;

                    // Removed
                case 2:
                    $edits[] = [
                        'start' => [
                            'line' => $lineNumber,
                            'character' => 0,
                        ],
                        'end' => [
                            'line' => $lineNumber + 1,
                            'character' => 0,
                        ],
                        'text' => '',
                    ];
                    $lineNumber--;
                    break;
            }
        }

        return $edits;
    }
}
