<?php

namespace Phpactor\WorseReflection\Core;

use Phpactor\WorseReflection\Core\Type\MissingType;

final class TemplateMap
{
    /**
     * @var array<string,Type>
     */
    private array $map;

    /**
     * @param array<string,Type> $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
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
}
