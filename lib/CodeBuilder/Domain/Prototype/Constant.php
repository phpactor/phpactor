<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

class Constant extends Prototype
{
    public function __construct(
        private readonly string $name,
        private readonly Value $value,
        private readonly ?Visibility $visibility = null,
        ?UpdatePolicy $updatePolicy = null
    ) {
        parent::__construct($updatePolicy);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): Value
    {
        return $this->value;
    }

    public function visibility(): ?Visibility
    {
        return $this->visibility;
    }
}
