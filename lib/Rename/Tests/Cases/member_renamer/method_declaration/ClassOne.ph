<?php

class ClassOne
{
    public function foobar(): string
    {
        return 'foobar';
    }

    #[\ReturnTypeWillChange]
    public function złom(): string
    {
        return 'f';
    }
}
