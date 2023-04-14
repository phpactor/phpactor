<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflector\ClassReflector;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Types;

class ClosureType extends ReflectedClassType implements ClassLikeType, InvokeableType
{
    private Type $returnType;

    /**
     * @param Type[] $args
     */
    public function __construct(ClassReflector $reflector, private array $args = [], ?Type $returnType = null)
    {
        parent::__construct($reflector, ClassName::fromString('Closure'));
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

    public function allTypes(): Types
    {
        return new Types([
            TypeFactory::reflectedClass($this->reflector, 'Closure'),
            ...$this->args,
            $this->returnType
        ]);
    }
}
