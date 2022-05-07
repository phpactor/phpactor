<?php

namespace Test;

class ClassOne
{
    public function __construct(public string $foobar)
    {
    }

    public function bar(): string
    {
        return $this->foobar;
    }
}
