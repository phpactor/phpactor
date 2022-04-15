<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\MissingType;

final class Variable
{
    private string $name;

    private Type $type;

    private ?Type $classType;

    private int $offset;

    public function __construct(string $name, int $offset, Type $type, ?Type $classType = null)
    {
        $this->name = ltrim($name, '$');
        $this->type = $type;
        $this->classType = $classType;
        $this->offset = $offset;
    }

    public function __toString()
    {
        return $this->name;
    }

    public static function fromSymbolContext(NodeContext $symbolContext): Variable
    {
        return new self(
            $symbolContext->symbol()->name(),
            $symbolContext->symbol()->position()->start(),
            $symbolContext->type(),
            $symbolContext->symbol()->symbolType() === Symbol::PROPERTY ? $symbolContext->containerType() : null
        );
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isNamed(string $name): bool
    {
        $name = ltrim($name, '$');

        return $this->name == $name;
    }

    public function withType(Type $type): self
    {
        return new self($this->name, $this->offset, $type, $this->classType);
    }

    public function withOffset(int $offset): self
    {
        return new self($this->name, $offset, $this->type, $this->classType);
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function isProperty(): bool
    {
        return null !== $this->classType;
    }

    public function classType(): Type
    {
        return $this->classType ?: new MissingType();
    }

    public function offset(): int
    {
        return $this->offset;
    }
}
