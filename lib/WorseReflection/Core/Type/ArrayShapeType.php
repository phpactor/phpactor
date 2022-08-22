<?php

namespace Phpactor\WorseReflection\Core\Type;

use Closure;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\TypeUtil;

class ArrayShapeType extends ArrayType implements Generalizable, ArrayAccessType
{
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
        $this->keyType = TypeUtil::generalTypeFromTypes(TypeFactory::fromValues(array_keys($typeMap)));
        $this->valueType = TypeUtil::generalTypeFromTypes($typeMap);
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

    public function generalize(): Type
    {
        return new self(array_map(fn (Type $type) => $type->generalize(), $this->typeMap));
    }

    /**
     * @param array-key $offset $offset
     */
    public function typeAtOffset($offset): Type
    {
        if (isset($this->typeMap[$offset])) {
            return $this->typeMap[$offset];
        }

        return new MissingType();
    }

    public function map(Closure $mapper): Type
    {
        return new self(
            array_map(function (Type $type) use ($mapper) {
                $type = $type->map($mapper);
                return $mapper($type);
            }, $this->typeMap)
        );
    }
}
