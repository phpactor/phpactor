<?php

namespace Phpactor;

class Foobar implements \Iterator
{
    /**
     * @var Barfoo
     */
    private $barfoo;

    /**
     * @var Foobar
     */
    private $foobar;

    public function __construct(Foobar $foobar, Barfoo $barfoo)
    {
        $this->barfoo = $barfoo;
        $this->foobar = $foobar;
    }
}





