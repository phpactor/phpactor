<?php

namespace Acme;

use Acme\Barfoo as ZedZed;
use Acme\Foobar\Barfoo;
use Acme\Foobar\Warble;

class Hello
{
    public function something(): void
    {
        $foo = new Warble();
        $bar = new Demo();

        //this should not be found as it is de-referenced (we wil replace the use statement instead)
        ZedZed::something();

        assert(Barfoo::class === 'Foo');
        Barfoo::foobar();
    }
}
