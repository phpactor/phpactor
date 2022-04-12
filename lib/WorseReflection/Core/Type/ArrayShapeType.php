<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Type;

class ArrayShapeType extends ArrayType
{
    use ArrayTypeTrait;

    /**
     * @var array<array-key,Type>
     */
    public array $typeMap;

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
        if ($this->isList()) {
            return sprintf(
                'array{%s}',
                implode(',', array_map(
                    fn (Type $t) => $t->__toString(),
                    $this->typeMap,
                ))
            );
        }

        return sprintf(
            'array{%s}',
            implode(',', array_map(
                fn (string $key, Type $t) => sprintf('%s:%s', $key, $t->__toString()),
                array_keys($this->typeMap),
                $this->typeMap
            ))
        );
    }

    public function isList(): bool
    {
        return range(0, count($this->typeMap) - 1) === array_keys($this->typeMap);
    }
}
