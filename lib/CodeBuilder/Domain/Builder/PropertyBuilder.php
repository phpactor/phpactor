<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Prototype\UpdatePolicy;
use Phpactor\CodeBuilder\Domain\Prototype\Visibility;
use Phpactor\CodeBuilder\Domain\Prototype\DefaultValue;
use Phpactor\CodeBuilder\Domain\Prototype\Property;

class PropertyBuilder extends AbstractBuilder implements NamedBuilder
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Visibility
     */
    protected $visibility;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var DefaultValue
     */
    protected $defaultValue;
    /**
     * @var SourceCodeBuilder
     */
    private $parent;

    public function __construct(ClassLikeBuilder $parent, string $name)
    {
        $this->parent = $parent;
        $this->name = $name;
    }

    public static function childNames(): array
    {
        return [];
    }

    public function visibility(string $visibility): PropertyBuilder
    {
        $this->visibility = Visibility::fromString($visibility);

        return $this;
    }

    public function type(string $type): PropertyBuilder
    {
        $this->type = Type::fromString($type);

        return $this;
    }

    public function defaultValue($value): PropertyBuilder
    {
        $this->defaultValue = DefaultValue::fromValue($value);

        return $this;
    }

    public function build(): Property
    {
        return new Property(
            $this->name,
            $this->visibility,
            $this->defaultValue,
            $this->type,
            UpdatePolicy::fromModifiedState($this->isModified())
        );
    }

    public function end(): ClassLikeBuilder
    {
        return $this->parent;
    }

    public function builderName(): string
    {
        return $this->name;
    }
}
