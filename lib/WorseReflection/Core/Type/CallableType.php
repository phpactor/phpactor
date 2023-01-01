<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;

class CallableType extends PrimitiveType implements InvokeableType
{
    private Type $returnType;

    /**
     * @param Type[] $args
     */
    public function __construct(private array $args = [], ?Type $returnType = null)
    {
        $this->returnType = $returnType ?? new MissingType();
    }

    public function __toString(): string
    {
        if ($this->returnType instanceof MissingType) {
            return sprintf(
                'callable(%s)',
                implode(',', array_map(fn (Type $type) => $type->__toString(), $this->args))
            );
        }
        return sprintf(
            'callable(%s): %s',
            implode(',', array_map(fn (Type $type) => $type->__toString(), $this->args)),
            $this->returnType->__toString()
        );
    }

    public function toPhpString(): string
    {
        return 'callable';
    }

    public function accepts(Type $type): Trinary
    {
        return Trinary::fromBoolean($type instanceof CallableType);
    }

    public function map(Closure $mapper): Type
    {
        $new = clone $this;
        $new->args = array_map(fn (Type $t) => $t->map($mapper), $this->args);
        $new->returnType = $this->returnType->map($mapper);
        return $new;
    }

    public function arguments(): array
    {
        return $this->args;
    }

    public function returnType(): Type
    {
        return $this->returnType;
    }

    public function allTypes(): Types
    {
        return new Types([
            ...$this->args,
            $this->returnType
        ]);
    }
}
