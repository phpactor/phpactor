<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\DefaultValue;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\DocBlockFactory;
use Phpactor\WorseReflection\Core\DocBlock\PlainDocblock;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type;

class VirtualReflectionParameter implements ReflectionParameter
{
    public function __construct(
        private string $name,
        private ReflectionFunctionLike $functionLike,
        private Type $inferredType,
        private Type $type,
        private DefaultValue $default,
        private bool $byReference,
        private ReflectionScope $scope,
        private ByteOffsetRange $position,
        private int $index
    ) {
    }

    public function scope(): ReflectionScope
    {
        return $this->scope;
    }

    public function position(): ByteOffsetRange
    {
        return $this->position;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function method(): ReflectionFunctionLike
    {
        return $this->functionLike;
    }

    public function functionLike(): ReflectionFunctionLike
    {
        return $this->functionLike;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function inferredType(): Type
    {
        return $this->inferredType;
    }

    public function default(): DefaultValue
    {
        return $this->default;
    }

    public function byReference(): bool
    {
        return $this->byReference;
    }

    public function isPromoted(): bool
    {
        return false;
    }

    public function isVariadic(): bool
    {
        return false;
    }

    public function index(): int
    {
        return $this->index;
    }

    public function docblock(): DocBlock
    {
        return new PlainDocblock('');
    }
}
