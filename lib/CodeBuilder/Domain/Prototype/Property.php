<?php

namespace Phpactor\CodeBuilder\Domain\Prototype;

final class Property extends Prototype
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @var DefaultValue
     */
    private $defaultValue;

    /**
     * @var Type
     */
    private $type;

    public function __construct(
        string $name,
        Visibility $visibility = null,
        DefaultValue $defaultValue = null,
        Type $type = null,
        UpdatePolicy $updatePolicy = null
    ) {
        parent::__construct($updatePolicy);
        $this->name = $name;
        $this->visibility = $visibility ?: Visibility::public();
        $this->defaultValue = $defaultValue ?: DefaultValue::none();
        $this->type = $type ?: Type::none();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function visibility(): Visibility
    {
        return $this->visibility;
    }

    public function defaultValue(): DefaultValue
    {
        return $this->defaultValue;
    }

    public function type(): Type
    {
        return $this->type;
    }
}
