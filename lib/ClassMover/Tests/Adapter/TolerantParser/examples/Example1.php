<?php

namespace Acme;

use Acme\Foobar\Warble;
use Acme\Foobar\Barfoo;
use Acme\Barfoo as ZedZed;

class Hello
{
    public function something(): void
    {
        $foo = new Warble();
        $bar = new Demo();

        //this should not be found as it is de-referenced (we will replace the use statement instead)
        ZedZed::something();

        assert(Barfoo::class === 'Foo');
        Barfoo::foobar();
    }
}
