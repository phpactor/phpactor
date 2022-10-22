<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Type;

class ClosureType extends ReflectedClassType implements ClassNamedType, InvokeableType
{
    /**
     * @var Type[]
     */
    private array $args;

    private Type $returnType;

    /**
     * @param Type[] $args
     */
    public function __construct(ClassReflector $reflector, array $args = [], ?Type $returnType = null)
    {
        parent::__construct($reflector, ClassName::fromString('Closure'));
        $this->args = $args;
        $this->returnType = $returnType ?? new MissingType();
    }

    public function __toString(): string
    {
        return sprintf(
            'Closure(%s): %s',
            implode(',', array_map(fn (Type $type) => $type->__toString(), $this->args)),
            $this->returnType->__toString()
        );
    }

    public function toPhpString(): string
    {
        return 'Closure';
    }

    public function name(): ClassName
    {
        return ClassName::fromString('Closure');
    }

    public function arguments(): array
    {
        return $this->args;
    }

    public function returnType(): Type
    {
        return $this->returnType;
    }

    public function map(Closure $mapper): Type
    {
        $new = clone $this;
        $new->args = array_map(fn (Type $t) => $t->map($mapper), $this->args);
        $new->returnType = $this->returnType->map($mapper);
        return $new;
    }
}
