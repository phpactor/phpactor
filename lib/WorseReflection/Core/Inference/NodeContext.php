<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;

final class NodeContext
{
    private Type $type;
    
    private Symbol $symbol;

    private TypeAssertions $typeAssertions;

    /**
     * @var Type
     */
    private ?Type $containerType = null;

    /**
     * @var string[]
     */
    private array $issues = [];

    /**
     * @var ReflectionScope
     */
    private ?ReflectionScope $scope = null;

    private function __construct(Symbol $symbol, Type $type, Type $containerType = null, ReflectionScope $scope = null)
    {
        $this->symbol = $symbol;
        $this->containerType = $containerType;
        $this->type = $type;
        $this->scope = $scope;
        $this->typeAssertions = new TypeAssertions([]);
    }

    public static function for(Symbol $symbol): NodeContext
    {
        return new self($symbol, TypeFactory::unknown());
    }

    public static function fromType(Type $type): NodeContext
    {
        return new self(Symbol::unknown(), $type);
    }

    public static function none(): NodeContext
    {
        return new self(Symbol::unknown(), new MissingType());
    }

    public function withContainerType(Type $containerType): NodeContext
    {
        $new = clone $this;
        $new->containerType = $containerType;

        return $new;
    }

    public function withTypeAssertions(TypeAssertions $typeAssertions): NodeContext
    {
        $new = clone $this;
        $new->typeAssertions = $typeAssertions;

        return $new;
    }

    public function withType(Type $type): NodeContext
    {
        $new = clone $this;
        $new->type = $type;

        return $new;
    }

    public function withTypeAssertion(TypeAssertion $typeAssertion): NodeContext
    {
        $new = clone $this;
        $new->typeAssertions = $new->typeAssertions->add($typeAssertion);

        return $new;
    }

    public function withScope(ReflectionScope $scope): NodeContext
    {
        $new = clone $this;
        $new->scope = $scope;

        return $new;
    }

    public function withIssue(string $message): NodeContext
    {
        $new = clone $this;
        $new->issues[] = $message;

        return $new;
    }

    /**
     * @deprecated
     */
    public function type(): Type
    {
        return $this->type ?? new MissingType();
    }

    public function symbol(): Symbol
    {
        return $this->symbol;
    }

    public function containerType(): Type
    {
        return $this->containerType ?: new MissingType();
    }

    /**
     * @return string[]
     */
    public function issues(): array
    {
        return $this->issues;
    }

    public function scope(): ReflectionScope
    {
        return $this->scope;
    }

    public function typeAssertions(): TypeAssertions
    {
        return $this->typeAssertions;
    }

    public function negateTypeAssertions(): self
    {
        foreach ($this->typeAssertions as $typeAssertion) {
            $typeAssertion->negate();
        }

        return $this;
    }
}
