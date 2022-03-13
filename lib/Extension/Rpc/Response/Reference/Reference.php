<?php

namespace Phpactor\Extension\Rpc\Response\Reference;

class Reference
{
    private int $start;

    private int $end;

    private int $lineNumber;

    private ?int $colNo;

    private string $line;

    private function __construct(int $start, int $end, int $lineNumber, int $colNo = null, string $line = '')
    {
        $this->start = $start;
        $this->end = $end;
        $this->lineNumber = $lineNumber;
        $this->colNo = $colNo;
        $this->line = $line;
    }

    public static function fromStartEndLineNumberAndCol(int $start, int $end, int $lineNumber, int $col = null)
    {
        return new self($start, $end, $lineNumber, $col);
    }

    public static function fromStartEndLineNumberLineAndCol(int $start, int $end, int $lineNumber, string $line, int $col = null)
    {
        return new self($start, $end, $lineNumber, $col, $line);
    }

    public function toArray()
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
            'line' => $this->line,
            'line_no' => $this->lineNumber,
            'col_no' => $this->colNo
        ];
    }
}
