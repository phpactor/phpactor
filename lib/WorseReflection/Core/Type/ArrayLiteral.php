<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
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
        $this->keyType = new MixedType();
        $this->valueType = $this->resolveValueType($typeMap);
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

    /**
     * @param array<array-key,Type> $typeMap
     */
    private function resolveValueType(array $typeMap): Type
    {
        $valueType = null;
        foreach ($typeMap as $type) {
            $type = TypeUtil::generalize($type);
            if ($valueType === null) {
                $valueType = $type;
                continue;
            }

            if ($valueType != $type) {
                return new MixedType();
            }
        }

        return $valueType ?: new MissingType();
    }
}
