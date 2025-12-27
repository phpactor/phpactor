<?php

declare(strict_types=1);

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Case_ extends Prototype
{
    public function __construct(
        private readonly string $name,
        private readonly ?Value $value = null,
        ?UpdatePolicy $updatePolicy = null
    ) {
        parent::__construct($updatePolicy);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): ?Value
    {
        return $this->value;
    }
}
