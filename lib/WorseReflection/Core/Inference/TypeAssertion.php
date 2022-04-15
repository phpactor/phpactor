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

    private int $offset;

    /**
     * @param self::VARIABLE_TYPE_* $variableType
     */
    private function __construct(string $variableType, string $name, int $offset, Closure $true, Closure $false, Type $classType = null)
    {
        $this->name = ltrim($name, '$');
        $this->variableType = $variableType;
        $this->classType = $classType;
        $this->true = $true;
        $this->false = $false;
        $this->offset = $offset;
    }

    public static function variable(string $name, int $offset, Closure $true, Closure $false): self
    {
        return new self(self::VARIABLE_TYPE_VARIABLE, $name, $offset, $true, $false, null);
    }

    public static function property(string $name, int $offset, Closure $true, Closure $false, Type $classType): self
    {
        return new self(self::VARIABLE_TYPE_PROPERTY, $name, $offset, $true, $false, $classType);
    }

    public static function forContext(NodeContext $context, Closure $true, Closure $false): self
    {
        if ($context->symbol()->symbolType() === Symbol::PROPERTY) {
            return TypeAssertion::property(
                $context->symbol()->name(),
                $context->symbol()->position()->start(),
                $true, 
                $false,
                $context->containerType() ?: new MissingType(),
            );
        }

        if ($context->symbol()->symbolType() === Symbol::VARIABLE) {
            return TypeAssertion::variable($context->symbol()->name(), $context->symbol()->position()->start(), $true, $false);
        }

        throw new RuntimeException(sprintf(
            'Do not know how to create type assertion for symbol type: "%s"',
            $context->type()->__toString()
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

    public function offset(): int
    {
        return $this->offset;
    }

    public function polarity(): bool
    {
        return $this->polarity;
    }
}
