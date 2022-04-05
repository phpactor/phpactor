<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\MissingType;

final class Variable
{
    private string $name;

    private Type $type;

    /**
     * @var mixed
     */
    private $value;

    private ?Type $classType;

    /**
     * @param mixed $value
     */
    private function __construct(string $name, Type $type, ?Type $classType = null, $value = null)
    {
        $this->name = $name;
        $this->type = $type;
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
            $symbolContext->type(),
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

    public function withType(Type $type): self
    {
        return new self($this->name, $type, $this->classType, $this->value);
    }

    public function type(): Type
    {
        return $this->type;
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
}
