<?php

namespace Phpactor\Rpc\Response\Reference;

class Reference
{
    /**
     * @var int
     */
    private $start;

    /**
     * @var int
     */
    private $end;

    /**
     * @var int
     */
    private $lineNumber;

    /**
     * @var int
     */
    private $colNo;

    private function __construct(int $start, int $end, int $lineNumber, int $colNo = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->lineNumber = $lineNumber;
        $this->colNo = $colNo;
    }

    public static function fromStartEndLineNumberAndCol(int $start, int $end, int $lineNumber, int $col = null)
    {
        return new self($start, $end, $lineNumber, $col);
    }

    public function toArray()
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
            'line_no' => $this->lineNumber,
            'col_no' => $this->colNo
        ];
    }
}
