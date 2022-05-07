<?php

class ClassOne
{
    private const BAR = "bar";

    public function foobar(): string
    {
        self::BAR;
    }
}
