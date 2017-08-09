<?php

namespace Phpactor;

class Test
{
    /**
     * @var Phpactor\Turtle
     */
    private $turtle;

    public function mutate()
    {
        $this->turtle = new Turtle();
    }
}
