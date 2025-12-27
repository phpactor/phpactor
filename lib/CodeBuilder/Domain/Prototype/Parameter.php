<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class Parameter extends Prototype
{
    private readonly Type $type;

    private readonly DefaultValue $defaultValue;

    public function __construct(
        private readonly string $name,
        ?Type $type = null,
        ?DefaultValue $defaultValue = null,
        private readonly bool $byReference = false,
        ?UpdatePolicy $updatePolicy = null,
        private readonly bool $isVariadic = false,
        private readonly ?Visibility $visibility = null
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
