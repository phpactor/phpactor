<?php

namespace Phpactor\TextDocument;

final class TextEditDiff
{
    private const REPLACE = 'r';
    private const NOOP = 'o';
    private const ADD = '+';
    private const DEL = '-';

    public function diff(string $one, string $two): TextEdits
    {
        $table = $this->lcsTable($one, $two);
        $ops = $this->resolveOps(
            $table,
            mb_str_split($one),
            mb_str_split($two),
            mb_strlen($one) - 1,
            mb_strlen($two) - 1
        );
        $edits = $this->textEdits($ops);

        return $edits;
    }

    /**
     * @return array<int,array<int, int>>
     */
    private function lcsTable(string $one, string $two): array
    {
        $m = mb_strlen($one);
        $n = mb_strlen($two);
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

    /**
     * @param array<int, array<int,int>> $table
     * @param list<string> $x
     * @param list<string> $y
     * @param list<array{string,string,int}> $ops
     * @return list<array{string,string,int}>
     */
    function resolveOps(array $table, array $x, array $y, int $i, int $j, array $ops = []): array {
        if ($i > 0 && $j > 0 && $x[$i] === $y[$j]) {
            $ops = $this->resolveOps($table, $x, $y, $i-1, $j-1);
            $ops[] = [self::NOOP, $x[$i], $i];
            return $ops;
        } 

        if ($j > 0 && ($i === 0 || $table[$i][$j-1] >= $table[$i-1][$j])) {
            $ops = $this->resolveOps($table, $x, $y, $i, $j-1);
            $ops[] = [self::ADD, $y[$j], $i + 1];
            return $ops;
        }
        
        if ($i > 0 && ($j === 0 || $table[$i][$j-1] < $table[$i-1][$j])) {
            $ops = $this->resolveOps($table, $x, $y, $i - 1, $j);
            $ops[] = [self::DEL, $x[$i], $i];
            return $ops;
        }

        if ($j === 0 && $i === 0 && $x[$i] !== $y[$j]) {
            $ops[] = [self::REPLACE, $y[$i], $i];
            return $ops;
        }

        return $ops;
    }

    /**
     * @param list<array{string,string,int}> $ops
     */
    private function textEdits(array $ops): TextEdits
    {
        $chunks = [];
        $currentOps = [];
        $currentOpName = null;
        $lastOp = null;

        // chunk by operation
        foreach ($ops as $op) {
            $opName = $op[0];

            if ($lastOp === null) {
                $currentOps[] = $op;
            } elseif ($opName != $lastOp) {
                $chunks[] = $currentOps;
                $currentOps = [$op];
            } else {
                $currentOps[] = $op;
            }

            $lastOp = $opName;
        }

        if ($currentOps) {
            $chunks[] = $currentOps;
        }

        // covert to text edits
        $edits = [];
        foreach ($chunks as $chunk) {
            if ($chunk[0][0] === self::ADD) {
                $edits[] = TextEdit::create(
                    $chunk[0][2],
                    0,
                    implode('', array_map(fn (array $op) => $op[1], $chunk))
                );
            }
            if ($chunk[0][0] === self::DEL) {
                $edits[] = TextEdit::create(
                    $chunk[0][2],
                    count($chunk),
                    '',
                );
            }
            if ($chunk[0][0] === self::REPLACE) {
                $edits[] = TextEdit::create(
                    $chunk[0][2],
                    count($chunk),
                    implode('', array_map(fn (array $ops) => $ops[1], $chunk)),
                );
            }
        }

        return TextEdits::fromTextEdits($edits);
    }
}
