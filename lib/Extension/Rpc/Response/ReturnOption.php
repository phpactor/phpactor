<?php

namespace Phpactor\Extension\Rpc\Response;

class ReturnOption
{
    private string $name;

    /**
     * @var mixed
     */
    private $value;

    private function __construct(string $name, $value)
    {
        $this->name = $name;
        $this->value = $value;
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
