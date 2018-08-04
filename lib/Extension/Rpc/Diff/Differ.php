<?php

namespace Phpactor\Extension\Rpc\Diff;

use SebastianBergmann\Diff\Differ as BergmannDiffer;

class Differ
{
    /**
     * @var BergmannDiffer
     */
    private $differ;

    public function __construct(BergmannDiffer $differ = null)
    {
        $this->differ = $differ ?: new BergmannDiffer();
    }

    public function chunkDiff(string $original, string $new)
    {
        $diff = $this->differ->diffToArray($original, $new);
        $offset = 0;

        foreach ($diff as $line) {
            $token = $line[0];
            switch ($line[1]) {
                // Nothing
                case 0:
                    break;

                // Added
                case 1:
                    $edits[] = [
                        'start' => $offset,
                        'length' => 0,
                        'text' => $token,
                    ];
                    break;

                // Removed
                case 2:
                    $edits[] = [
                        'start' => $offset,
                        'length' => strlen($token),
                        'text' => '',
                    ];
                    $offset -= strlen($token);
                    break;
            }
            $offset = $offset + strlen($token);
        }

        return $edits;
    }
}
