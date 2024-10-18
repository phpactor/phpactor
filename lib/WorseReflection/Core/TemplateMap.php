<?php

namespace Phpactor\WorseReflection\Core;

use Countable;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\MissingType;

final class TemplateMap implements Countable
{
    /**
     * @param array<string,Type> $map
     */
    public function __construct(private array $map)
    {
    }

    public function __toString(): string
    {
        return implode("\n", array_map(fn (string $name, Type $type) => sprintf('%s: %s', $name, $type->__toString()), array_keys($this->map), $this->map));
    }

    /**
     * @return array<string,Type>
     */
    public function toArray(): array
    {
        return $this->map;
    }

    public function replace(string $key, Type $type): self
    {
        $this->map[$key] = $type;

        return $this;
    }

    public function has(string $key): bool
    {
        return isset($this->map[$key]);
    }

    /**
     * @param Type[] $arguments
     */
    public function get(string $key, array $arguments = []): Type
    {
        if (!isset($this->map[$key])) {
            return new MissingType();
        }

        // if any of the arguments are template parameters replace them with
        // any constraints (e.g. T of Foobar)
        $arguments = array_map(function (Type $argument) {
            return $this->map[$argument->short()] ?? $argument;
        }, $arguments);

        if ($arguments) {
            $offset = array_search($key, array_keys($this->map));

            if (isset($arguments[$offset])) {
                return $arguments[$offset];
            }
        }

        return $this->map[$key];
    }

    public function merge(TemplateMap $map): TemplateMap
    {
        $new = $this->map;
        foreach ($map->map as $key => $value) {
            $new[$key] = $value;
        }


        return new TemplateMap($new);
    }

    public function count(): int
    {
        return count($this->map);
    }

    /**
     * @param Type[] $arguments
     */
    public function mapArguments(array $arguments): TemplateMap
    {
        $newMap = [];
        foreach ($this->map as $key => $type) {
            $argument = array_shift($arguments);
            if (null === $argument) {
                $newMap[$key] = $type;
                continue;
            }
            $newMap[$key] = $argument;
        }

        return new self($newMap);
    }

    /**
     * @return Type[]
     */
    public function toArguments(): array
    {
        return array_values($this->map);
    }

    public function getOrGiven(Type $type): Type
    {
        if (!$type instanceof ClassType) {
            return $type;
        }
        $templateType = $this->map[$type->short()] ?? null;
        if ($templateType) {
            return $templateType;
        }
        return $type;
    }
}
