<?php

namespace Acme\ClassMover\Tests\Adapter\TolerantParser;

class Example7
{
    public function build(): Example7
    {
        return new self();
    }
}
