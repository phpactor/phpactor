<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;

final class TypeAssertion
{
    private string $name;
    private Type $type;

    private function __construct(string $name, Type $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public static function variable(string $name, Type $type): self
    {
        return new self($name, $type);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function withType(Type $type): self
    {
        return new self($this->name, $type);
    }
}
