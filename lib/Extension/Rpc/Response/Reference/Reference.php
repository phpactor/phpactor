<?php

namespace Phpactor\Extension\Rpc\Response\Reference;

class Reference
{
    private function __construct(
        private readonly int $start,
        private readonly int $end,
        private readonly int $lineNumber,
        private readonly ?int $colNo = null,
        private readonly string $line = ''
    ) {
    }

    public static function fromStartEndLineNumberLineAndCol(int $start, int $end, int $lineNumber, string $line, ?int $col = null)
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
