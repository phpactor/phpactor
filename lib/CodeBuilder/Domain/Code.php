<?php

namespace Phpactor\CodeBuilder\Domain;

class Code
{
    private function __construct(private string $code)
    {
    }

    public function __toString()
    {
        return $this->code;
    }

    public static function fromString(string $string)
    {
        return new self($string);
    }
}
