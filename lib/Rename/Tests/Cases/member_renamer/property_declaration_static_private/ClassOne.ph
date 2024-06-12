<?php

class ClassOne
{
    private static string $foobar;

    public function __construct(string $foobar)
    {
        self::$foobar = $foobar;
    }

    public function foobar(): string
    {
        return self::$foobar;
    }
}
