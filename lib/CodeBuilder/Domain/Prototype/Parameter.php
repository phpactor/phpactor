<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class Parameter extends Prototype
{
    /**
     * @var bool
     */
    private $byReference;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Type
     */
    private $type;

    /**
     * @var DefaultValue
     */
    private $defaultValue;

    public function __construct(
        string $name,
        Type $type = null,
        DefaultValue $defaultValue = null,
        bool $byReference = false,
        UpdatePolicy $updatePolicy = null
    ) {
        parent::__construct($updatePolicy);
        $this->name = $name;
        $this->type = $type ?: Type::none();
        $this->defaultValue = $defaultValue ?: DefaultValue::none();
        $this->byReference = $byReference;
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
}
