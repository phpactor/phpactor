<?php

namespace Phpactor\TextDocument;

class TextEditDiff
{
    public function diff(string $one, string $two): TextEdits
    {
        $table = $this->lcsTable($one, $two);
        $diff = $this->printDiff(
            $table,
            str_split($one),
            str_split($two),
            strlen($one) - 1,
            strlen($two) - 1
        );
        return TextEdits::none();
    }

    /**
     * @return array<int,array<int, int>>
     */
    private function lcsTable(string $one, string $two): array
    {
        $m = strlen($one);
        $n = strlen($two);
        $table = [];

        for ($i = 0; $i <= $m; $i++) {
            $table[$i][0] = 0;
        }
        for ($j = 0; $j <= $n; $j++) {
            $table[0][$j] = 0;
        }

        for ($i = 1; $i <= $m; $i++) {
            for ($j = 1; $j <= $n; $j++) {
                if (substr($one, $i - 1, 1) === substr($two, $j - 1, 1)) {
                    $table[$i][$j] = $table[$i - 1][$j - 1] + 1;
                } else {
                    $table[$i][$j] = max($table[$i][$j - 1], $table[$i - 1][$j]);
                }
            }
        }

        return $table;
    }

    function printDiff(array $table, array $x, array $y, int $i, int $j): void {
        if ($i >= 0 && $j >= 0 && $x[$i] === $y[$j]) {
            $this->printDiff($table, $x, $y, $i-1, $j-1);
            echo $x[$i];
            return;
        } 
        
        if ($j > 0 && ($i === 0 or $table[$i][$j-1] >= $table[$i-1][$j])) {
            $this->printDiff($table, $x, $y, $i, $j-1);
            print "+" . $y[$j];
            return;
        }
        
        if ($i > 0 && ($j === 0 || $table[$i][$j-1] < $table[$i-1][$j])) {
            $this->printDiff($table, $x, $y, $i - 1, $j);
            echo "-" . $x[$i];
        }
    }
}
