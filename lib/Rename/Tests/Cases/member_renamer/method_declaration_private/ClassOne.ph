<?php

namespace Foo;

class ClassOne
{
    private function foobar(): string
    {
        return 'foobar';
    }

    public function hello(): string
    {
        return $this->foobar();
    }
}
