<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\TypeUtil;

class ArrayLiteral extends ArrayType implements Literal, Generalizable
{
    /**
     * @var array<array-key,Type>
     */
    private array $typeMap;

    /**
     * @param array<array-key,Type> $typeMap
     */
    public function __construct(array $typeMap)
    {
        $this->typeMap = $typeMap;
    }

    public function __toString(): string
    {
        return sprintf(
            'array{%s}',
            implode(',', array_map(
                fn ($key, Type $type) => sprintf('%s:%s', $key, $type->__toString()),
                array_keys($this->typeMap),
                array_values($this->typeMap),
            ))
        );
    }

    /**
     * {@inheritDoc}
     */
    public function value()
    {
        return array_map(
            fn (Type $type) => TypeUtil::valueOrNull($type),
            $this->typeMap
        );
    }

    public function generalize(): Type
    {
        return new ArrayType(new ArrayKeyType(), new MixedType());
    }
}
