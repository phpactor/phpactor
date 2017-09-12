<?php

namespace Phpactor\Rpc\Editor\Reference;

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

    private function __construct(int $start, int $end, int $lineNumber)
    {
        $this->start = $start;
        $this->end = $end;
        $this->lineNumber = $lineNumber;
    }

    public static function fromStartEndAndLineNumber(int $start, int $end, int $lineNumber)
    {
        return new self($start, $end, $lineNumber);
    }

    public function toArray()
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
            'line_no' => $this->lineNumber
        ];
    }
}

