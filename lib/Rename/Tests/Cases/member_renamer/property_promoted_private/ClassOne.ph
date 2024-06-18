<?php

class ClassOne
{
    private string $barfoo;

    public function __construct(
        private string $foobar,
    ) {
        $this->barfoo = 'barfoo';
    }

    public function bar(): string
    {
        return $this->foobar;
    }

    public function foo(): string
    {
        return $this->barfoo;
    }
}
