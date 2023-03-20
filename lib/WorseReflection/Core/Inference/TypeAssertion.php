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

    private Closure $true;

    private Closure $false;

    private bool $polarity = true;

    /**
     * @param self::VARIABLE_TYPE_* $variableType
     */
    private function __construct(
        private string $variableType,
        string $name,
        private int $offset,
        Closure $true,
        Closure $false,
        private ?Type $classType = null
    ) {
        $this->name = ltrim($name, '$');
        $this->true = $true;
        $this->false = $false;
    }

    public function __toString()
    {
        return sprintf(
            '%s: %s#%s %s',
            $this->variableType(),
            $this->name(),
            $this->offset(),
            $this->polarity() ? 'positive' : 'negative',
        );
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
                $context->symbol()->position()->start()->asInt(),
                $true,
                $false,
                $context->containerType(),
            );
        }

        if ($context->symbol()->symbolType() === Symbol::VARIABLE) {
            return TypeAssertion::variable($context->symbol()->name(), $context->symbol()->position()->start()->asInt(), $true, $false);
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

    public function negate(): TypeAssertion
    {
        $this->polarity = !$this->polarity;
        return $this;
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
