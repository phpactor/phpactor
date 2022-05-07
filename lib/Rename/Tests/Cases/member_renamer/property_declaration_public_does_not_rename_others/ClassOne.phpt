<?php

class ClassOne
{
    public string $foobar;
    public string $barfoo;
    private string $bazbar;

    public function __construct(string $foobar)
    {
        $this->foobar = $foobar;
    }
}
