<?php

class ClassOne
{
    private string $foobar;

    public function __construct(string $foobar)
    {
        $this->foobar = $foobar;
    }

    public function foobar(): string
    {
        return $this->foobar;
    }
}
