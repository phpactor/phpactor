<?php

class ClassTwo
{
    public function hello(): string
    {
        return (new ClassOne())->foobar();
    }
}
