<?php

namespace Phpactor;

class Demo
{
    public function hello()
    {
        $var1 = 1234;
        $var2 = 'abcd';
        $var3 = new \stdClass;

        echo 'hello';
        $bar = 'goodbye';
        $var3->hello;
        $var2 = 'dcba';

        echo $var2;

        return $bar;
    }
}
