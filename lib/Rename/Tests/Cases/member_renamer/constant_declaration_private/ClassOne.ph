<?php

class ClassOne
{
    private const BAR = "bar";

    public function foobar(): string
    {
        return self::BAR;
    }
}
