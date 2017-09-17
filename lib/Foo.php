<?php

namespace Phpactor;

final class Foo
{
    private $foo;

    private function __construct($foo)
    {
        $this->foo = $foo;
    }

    public static function fromString(string $foo): Foo
    {
         return new self($foo);
    }

    public function __toString()
    {
        return $this->foo;
    }
}