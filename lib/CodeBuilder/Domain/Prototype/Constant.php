<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Constant extends Prototype
{
    private string $name;

    private $value;

    public function __construct(string $name, Value $value, UpdatePolicy $updatePolicy = null)
    {
        parent::__construct($updatePolicy);
        $this->name = $name;
        $this->value = $value;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): Value
    {
        return $this->value;
    }
}
