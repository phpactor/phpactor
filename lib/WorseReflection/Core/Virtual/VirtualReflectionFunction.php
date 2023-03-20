<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\PlainDocblock;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Position;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\MissingType;

final class VirtualReflectionFunction implements ReflectionFunction
{
    public function __construct(
        private Position $position,
        private NodeText $text,
        private Frame $frame,
        private DocBlock $docblock,
        private ReflectionScope $scope,
        private Type $inferredType,
        private Type $type,
        private SourceCode $source,
        private Name $name,
    ) {}

    public static function empty(
        Name $name,
        Position $position
    ) {
        return new self(
            $position,
            NodeText::fromString(''),
            new Frame(name: 'foo'),
            new PlainDocblock(),
            new DummyReflectionScope(),
            TypeFactory::undefined(),
            TypeFactory::undefined(),
            new SourceCode(''),
            $name
        );
    }


    public function parameters(): ReflectionParameterCollection
    {
        return $this->parameters;
    }

    public function body(): NodeText
    {
        return $this->body;
    }

    public function position(): Position
    {
        return $this->position;
    }

    public function frame(): Frame
    {
        return $this->frame;
    }

    public function docblock(): DocBlock
    {
        return $this->frame;
    }

    public function scope(): ReflectionScope
    {
        return $this->docblock;
    }

    public function inferredType(): Type
    {
        return $this->inferredType;
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function sourceCode(): SourceCode
    {
        return $this->sourceCode;
    }

    public function name(): Name
    {
        return $this->name;
    }
}
