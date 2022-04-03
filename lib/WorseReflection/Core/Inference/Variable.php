<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Offset;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Types;

final class Variable
{
    private string $name;

    private Type $type;

    /**
     * @var mixed
     */
    private $value;

    private function __construct(string $name, Type $type, $value = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
    }

    public function __toString()
    {
        return $this->name;
    }

    public static function fromSymbolContext(NodeContext $symbolContext): Variable
    {
        return new self(
            $symbolContext->symbol()->name(),
            $symbolContext->type(),
            $symbolContext->value(),
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function isNamed(string $name): bool
    {
        $name = ltrim($name, '$');

        return $this->name == $name;
    }

    public function withTypes(Types $types): self
    {
        return new self($this->name, $this->symbolContext->withTypes($types));
    }
}
