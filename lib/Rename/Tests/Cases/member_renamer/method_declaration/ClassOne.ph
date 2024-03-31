<?php

class ClassOne
{
    #[ReturnTypeWillChange]
    public function foobar(): string
    {
        return 'foobar';
    }
}
