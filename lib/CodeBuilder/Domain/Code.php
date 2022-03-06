<?php

namespace Phpactor\CodeBuilder\Domain;

class Code
{
    private $code;

    private function __construct(string $code)
    {
        $this->code = $code;
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
