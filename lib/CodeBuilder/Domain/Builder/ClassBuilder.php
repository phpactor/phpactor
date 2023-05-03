<?php

namespace Phpactor\CodeBuilder\Domain\Builder;

use Phpactor\CodeBuilder\Domain\Prototype\ClassPrototype;
use Phpactor\CodeBuilder\Domain\Prototype\ExtendsClass;
use Phpactor\CodeBuilder\Domain\Prototype\Properties;
use Phpactor\CodeBuilder\Domain\Prototype\Type;
use Phpactor\CodeBuilder\Domain\Prototype\Methods;
use Phpactor\CodeBuilder\Domain\Prototype\ImplementsInterfaces;
use Phpactor\CodeBuilder\Domain\Prototype\Constants;
use Phpactor\CodeBuilder\Domain\Prototype\UpdatePolicy;

class ClassBuilder extends ClassLikeBuilder
{
    /**
     * @var PropertyBuilder[]
     */
    protected array $properties = [];

    /**
     * @var Type[]
     */
    protected array $interfaces = [];

    /**
     * @var ConstantBuilder[]
     */
    protected array $constants = [];

    private ?ExtendsClass $extends = null;

    public static function childNames(): array
    {
        return array_merge(parent::childNames(), [
            'properties',
            'constants',
        ]);
    }

    public function extends(string $class): ClassBuilder
    {
        $this->extends = ExtendsClass::fromString($class);

        return $this;
    }

    public function add(Builder $builder): void
    {
        if ($builder instanceof PropertyBuilder) {
            $this->properties[$builder->builderName()] = $builder;
            return;
        }

        parent::add($builder);
    }

    public function implements(string $interface): ClassBuilder
    {
        $this->interfaces[] = Type::fromString($interface);

        return $this;
    }

    public function property(string $name): PropertyBuilder
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name];
        }

        $this->properties[$name] = $builder = new PropertyBuilder($this, $name);

        return $builder;
    }

    public function constant(string $name, $value): ConstantBuilder
    {
        $this->constants[] = $builder = new ConstantBuilder($this, $name, $value);

        return $builder;
    }

    public function build(): ClassPrototype
    {
        return new ClassPrototype(
            $this->name,
            Properties::fromProperties(array_map(function (PropertyBuilder $builder) {
                return $builder->build();
            }, $this->properties)),
            Constants::fromConstants(array_map(function (ConstantBuilder $builder) {
                return $builder->build();
            }, $this->constants)),
            Methods::fromMethods(array_map(function (MethodBuilder $builder) {
                return $builder->build();
            }, $this->methods)),
            $this->extends,
            ImplementsInterfaces::fromTypes($this->interfaces),
            UpdatePolicy::fromModifiedState($this->isModified()),
            $this->docblock
        );
    }
}
