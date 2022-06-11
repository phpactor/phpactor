<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;

class VarDocBuffer
{
    /**
     * @var array<string,Type>
     */
    private array $buffer = [];

    private int $version = 0;

    public function set(string $name, Type $type): void
    {
        $this->version++;
        $this->buffer[$name] = $type;
    }

    public function yank(string $name): ?Type
    {
        if (!isset($this->buffer[$name])) {
            return null;
        }
        $type = $this->buffer[$name];

        unset($this->buffer[$name]);

        return $type;
    }

    public function version(): int
    {
        return $this->version;
    }
}
