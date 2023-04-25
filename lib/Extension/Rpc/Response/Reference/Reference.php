<?php

namespace Phpactor\Extension\Rpc\Response\Reference;

class Reference
{
    private function __construct(
        private int $start,
        private int $end,
        private int $lineNumber,
        private ?int $colNo = null,
        private string $line = ''
    ) {
    }

    public static function fromStartEndLineNumberAndCol(int $start, int $end, int $lineNumber, int $col = null): self
    {
        return new self($start, $end, $lineNumber, $col);
    }

    public static function fromStartEndLineNumberLineAndCol(
        int $start,
        int $end,
        int $lineNumber,
        string $line,
        int $col = null
    ):self {
        return new self($start, $end, $lineNumber, $col, $line);
    }

    /**
     * @return array{
     *  start: int,
     *  end: int,
     *  line: string,
     *  line_no: int,
     *  col_no: int|null
     * }
    */
    public function toArray(): array
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
