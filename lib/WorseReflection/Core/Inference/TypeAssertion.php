<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Closure;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\MissingType;
use RuntimeException;

final class TypeAssertion
{
    const VARIABLE_TYPE_PROPERTY = 'property';
    const VARIABLE_TYPE_VARIABLE = 'variable';

    private string $name;

    /**
     * @var self::VARIABLE_TYPE_*
     */
    private string $variableType;

    private ?Type $classType;

    private Closure $true;

    private Closure $false;

    private bool $polarity = true;

    /**
     * @param self::VARIABLE_TYPE_* $variableType
     */
    private function __construct(string $variableType, string $name, Closure $true, Closure $false, Type $classType = null)
    {
        $this->name = ltrim($name, '$');
        $this->variableType = $variableType;
        $this->classType = $classType;
        $this->true = $true;
        $this->false = $false;
    }

    public static function variable(string $name, Closure $true, Closure $false): self
    {
        return new self(self::VARIABLE_TYPE_VARIABLE, $name, $true, $false, null);
    }

    public static function property(string $name, Closure $true, Closure $false, Type $classType): self
    {
        return new self(self::VARIABLE_TYPE_PROPERTY, $name, $true, $false, $classType);
    }

    public static function forContext(NodeContext $context, Closure $true, Closure $false): self
    {
        if ($context->symbol()->symbolType() === Symbol::PROPERTY) {
            return TypeAssertion::property(
                $context->symbol()->name(),
                $true, 
                $false,
                $context->containerType() ?: new MissingType(),
            );
        }

        if ($context->symbol()->symbolType() === Symbol::VARIABLE) {
            return TypeAssertion::variable($context->symbol()->name(), $true, $false);
        }

        throw new RuntimeException(sprintf(
            'Do not know how to create type assertion for symbol type: "%s"',
            $context->sumbol()->type()
        ));
    }

    public function name(): string
    {
        return $this->name;
    }

    public function apply(Type $type): Type
    {
        if ($this->polarity === true) {
            $true = $this->true;
            return $true($type);
        }

        $false = $this->false;
        return $false($type);
    }

    public function variableType(): string
    {
        return $this->variableType;
    }

    public function classType(): Type
    {
        return $this->classType ?: new MissingType();
    }

    public function negate(): void
    {
        $this->polarity = !$this->polarity;
    }

    private function withTypeAssertion(TypeAssertion $typeAssertion)
    {
    }
}
