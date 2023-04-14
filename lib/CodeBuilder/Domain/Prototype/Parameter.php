<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class Parameter extends Prototype
{
    private Type $type;

    private DefaultValue $defaultValue;

    public function __construct(
        private string $name,
        Type $type = null,
        DefaultValue $defaultValue = null,
        private bool $byReference = false,
        UpdatePolicy $updatePolicy = null,
        private bool $isVariadic = false,
        private ?Visibility $visibility = null
    ) {
        parent::__construct($updatePolicy);
        $this->type = $type ?: Type::none();
        $this->defaultValue = $defaultValue ?: DefaultValue::none();
        $this->updatePolicy = $updatePolicy;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function defaultValue(): DefaultValue
    {
        return $this->defaultValue;
    }

    public function byReference(): bool
    {
        return $this->byReference;
    }

    public function visibility(): ?Visibility
    {
        return $this->visibility;
    }

    public function isVariadic(): bool
    {
        return $this->isVariadic;
    }
}
