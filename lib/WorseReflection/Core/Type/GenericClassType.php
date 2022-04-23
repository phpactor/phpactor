<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\Resolver\IterableTypeResolver;

class GenericClassType extends ReflectedClassType implements IterableType
{
    /**
     * @var Type[]
     */
    public array $arguments;

    /**
     * @param Type[] $arguments
     */
    public function __construct(ClassReflector $reflector, ClassName $name, array $arguments)
    {
        parent::__construct($reflector, $name);
        $this->arguments = $arguments;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s<%s>',
            $this->name->__toString(),
            implode(',', array_map(fn (Type $t) => $t->__toString(), $this->arguments))
        );
    }

    /**
     * @return Type[]
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    public function iterableValueType(): Type
    {
        return IterableTypeResolver::resolveIterable($this, $this->arguments);
    }

    public function toPhpString(): string
    {
        return $this->name->__toString();
    }

    public function accepts(Type $type): Trinary
    {
        if ($this->is($type)->isTrue()) {
            return Trinary::true();
        }

        return Trinary::false();
    }

    public function replaceArgument(int $offset, Type $type): self
    {
        if (!isset($this->arguments[$offset])) {
            return $this;
        }

        $this->arguments[$offset] = $type;
        return $this;
    }

    /**
     * @param Type[] $arguments
     */
    public function setArguments(array $arguments): self
    {
        $this->arguments = $arguments;
        return $this;
    }

    public function iterableKeyType(): Type
    {
        return new MissingType();
    }

    protected function map(Closure $mapper): Type
    {
        return new self(
            $this->reflector,
            ClassName::fromString((new ReflectedClassType($this->reflector, $this->name))->map($mapper)->__toString()),
            array_map(fn (Type $type) => $type->map($mapper), $this->arguments)
        );
    }
}
