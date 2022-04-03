<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\NotType;
use Phpactor\WorseReflection\Core\Types;

final class Variable
{
    private string $name;

    private Types $types;

    /**
     * @var mixed
     */
    private $value;

    private ?Type $classType;

    /**
     * @param mixed $value
     */
    public function __construct(string $name, Types $types, ?Type $classType = null, $value = null)
    {
        $this->name = $name;
        $this->types = $types;
        $this->value = $value;
        $this->classType = $classType;
    }

    public function __toString()
    {
        return $this->name;
    }

    public static function fromSymbolContext(NodeContext $symbolContext): Variable
    {
        return new self(
            $symbolContext->symbol()->name(),
            $symbolContext->types(),
            $symbolContext->symbol()->symbolType() === Symbol::PROPERTY ? $symbolContext->containerType() : null,
            $symbolContext->value(),
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

    public function withTypes(Types $types): self
    {
        return new self($this->name, $types, $this->classType, $this->value);
    }

    public function withType(Type $type): self
    {
        return new self($this->name, Types::fromTypes([$type]), $this->classType, $this->value);
    }

    public function withClassType(Type $classType): self
    {
        return new self($this->name, $this->types, $classType, $this->value);
    }

    public function type(): Type
    {
        return $this->types->best();
    }

    public function types(): Types
    {
        return $this->types;
    }

    /**
     * @return mixed
     */
    public function value()
    {
        return $this->value;
    }

    public function isProperty(): bool
    {
        return null !== $this->classType;
    }

    public function classType(): Type
    {
        return $this->classType ?: new MissingType();
    }

    public function mergeType(Type $type): self
    {
        return $this->withType(TypeCombinator::merge($this->type(), $type));
    }
}
