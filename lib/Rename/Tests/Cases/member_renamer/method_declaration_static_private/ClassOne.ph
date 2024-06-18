<?php

class ClassOne
{

    public function hello(): string
    {
        return self::foobar();
    }
    private static function foobar(): string
    {
        return 'foobar';
    }
}
