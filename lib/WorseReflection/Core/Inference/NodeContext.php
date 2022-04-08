<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Types;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;

final class NodeContext
{
    private Type $type;
    
    private Symbol $symbol;

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

    public function withType(Type $type): NodeContext
    {
        $new = clone $this;
        $new->type = $type;

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

    public function hasContainerType(): bool
    {
        return null !== $this->containerType;
    }

    /**
     * @return Type|null
     */
    public function containerType()
    {
        return $this->containerType;
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
}
