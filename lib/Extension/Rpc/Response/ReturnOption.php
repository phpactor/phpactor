<?php

namespace Phpactor\Extension\Rpc\Response;

class ReturnOption
{
    private function __construct(
        private readonly string $name,
        private $value
    ) {
    }

    public static function fromNameAndValue(string $name, $value)
    {
        return new self($name, $value);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value()
    {
        return $this->value;
    }
}
