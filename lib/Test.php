<?php

namespace Phpactor;

use Symfony\Component\Console\Input\InputInterface;

class Test implements InputInterface
{

    public function __construct(\stdClass $foo)
    {
        $this->foo = 'foo';
        $this->baz = $foo;
    }

    public function foobar(Foobar $foobar, BarFoo $barfoo, $arse = 'string')
    {
        $this->foobar = $foobar;
        $this->barfoo = $barfoo;
        $this->arse = $arse;
        $this->boo = 'boo';
    }
}
