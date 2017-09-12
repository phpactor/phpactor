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

    private function __construct(int $start, int $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public static function fromStartAndEnd(int $start, int $end)
    {
        return new self($start, $end);
    }

    public function toArray()
    {
        return [
            'start' => $this->start,
            'end' => $this->end,
        ];
    }
}

