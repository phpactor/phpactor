<?php

namespace Phpactor;

class Foobar
{
    /**
     * @var Transformers
     */
    private $foobar;

    public function __construct(Transformers $foobar)
    {
        $this->foobar = $foobar;
    }
}

