<?php

namespace Phpactor\Extension\Rpc\Diff;

use SebastianBergmann\Diff\Differ as BergmannDiffer;

class TextEditBuilder
{
    /**
     * @var BergmannDiffer
     */
    private $differ;

    public function __construct(BergmannDiffer $differ = null)
    {
        $this->differ = $differ ?: new BergmannDiffer();
    }

    public function calculateTextEdits(string $original, string $new)
    {
        // TODO: Test for empty edits
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
                        'length' => 0,
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
                        'length' => strlen($token),
                        'text' => '',
                    ];
                    $lineNumber--;
                    break;
            }
        }

        return $edits;
    }
}
