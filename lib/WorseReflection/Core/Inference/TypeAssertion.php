<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\MissingType;

final class TypeAssertion
{
    const VARIABLE_TYPE_PROPERTY = 'property';
    const VARIABLE_TYPE_VARIABLE = 'variable';

    private string $name;

    private Type $type;

    /**
     * @var self::VARIABLE_TYPE_*
     */
    private string $variableType;

    private ?Type $classType;

    /**
     * @param self::VARIABLE_TYPE_* $variableType
     */
    private function __construct(string $variableType, string $name, Type $type, ?Type $classType)
    {
        $this->name = $name;
        $this->type = $type;
        $this->variableType = $variableType;
        $this->classType = $classType;
    }

    public static function variable(string $name, Type $type): self
    {
        return new self(self::VARIABLE_TYPE_VARIABLE, $name, $type, null);
    }

    public static function property(string $name, Type $classType, Type $type): self
    {
        return new self(self::VARIABLE_TYPE_PROPERTY, $name, $type, $classType);
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
        return new self($this->variableType, $this->name, $type, $this->classType);
    }

    public function variableType(): string
    {
        return $this->variableType;
    }

    public function classType(): Type
    {
        return $this->classType ?: new MissingType();
    }
}
