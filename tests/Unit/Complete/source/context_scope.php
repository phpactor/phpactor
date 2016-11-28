<?php

namespace Foobar\Barfoo;

$noScope = 'global scope here';

function foobar() {

    $functionScope = 'function scope';

}

class Test
{
    private $classScope;

    public function foobar()
    {
        $methodScope = 'method scope';
    }

    private $moreScope;

    public function barbar()
    {
        echo "Hi";
    }
}

echo "Global again";
