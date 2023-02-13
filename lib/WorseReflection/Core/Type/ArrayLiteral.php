<?php

namespace Phpactor\WorseReflection\Core\Type;

use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\TypeUtil;

class ArrayLiteral extends ArrayType implements Literal, Generalizable, ArrayAccessType
{
    /**
     * @param array<array-key,Type> $typeMap
     */
    public function __construct(private array $typeMap)
    {
        $this->keyType = TypeUtil::generalTypeFromTypes($this->iterableKeyTypes());
        $this->valueType = TypeUtil::generalTypeFromTypes(array_values($typeMap));
    }

    public function __toString(): string
    {
        if ($this->isList()) {
            return sprintf(
                'array{%s}',
                implode(',', array_map(
                    fn (Type $type) => sprintf('%s', $type->__toString()),
                    array_values($this->typeMap),
                ))
            );
        }

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
     * @return Type[]
     */
    public function iterableValueTypes(): array
    {
        return array_values($this->typeMap);
    }

    /**
     * @return Type[]
     */
    public function iterableKeyTypes(): array
    {
        return TypeFactory::fromValues(array_keys($this->typeMap));
    }

    public function isList(): bool
    {
        return range(0, count($this->typeMap) - 1) === array_keys($this->typeMap);
    }

    /**
     * @return mixed[]
     */
    public function value(): array
    {
        return array_map(
            fn (Type $type) => TypeUtil::valueOrNull($type),
            $this->typeMap
        );
    }

    public function generalize(): Type
    {
        return new ArrayType($this->keyType, $this->valueType);
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

    public function withValue(mixed $value): self
    {
        return $this;
    }

    /**
     * @return array<array-key,Type>
     */
    public function types(): array
    {
        return $this->typeMap;
    }

    /**
     * @param array-key $key
     */
    public function set($key, Type $type): self
    {
        $map = $this->typeMap;
        $map[$key] = $type;
        return new self($map);
    }

    public function add(Type $type): self
    {
        $map = $this->typeMap;
        $map[] = $type;
        return new self($map);
    }

    public function toShape(): ArrayShapeType
    {
        return new ArrayShapeType($this->typeMap);
    }

    public function accepts(Type $type): Trinary
    {
        if ($type instanceof ArrayType) {
            return Trinary::maybe();
        }

        return parent::accepts($type);
    }
}
