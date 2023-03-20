<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\PlainDocblock;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

final class VirtualReflectionFunction implements ReflectionFunction
{
    public function __construct(
        private ByteOffsetRange $range,
        private NodeText $body,
        private Frame $frame,
        private DocBlock $docblock,
        private ReflectionScope $scope,
        private Type $inferredType,
        private Type $type,
        private SourceCode $source,
        private Name $name,
        private ReflectionParameterCollection $parameters,
    ) {
    }

    public static function empty(
        Name $name,
        ByteOffsetRange $range
    ): self {
        return new self(
            $range,
            NodeText::fromString(''),
            new Frame(name: 'foo'),
            new PlainDocblock(),
            new DummyReflectionScope(),
            TypeFactory::undefined(),
            TypeFactory::undefined(),
            SourceCode::empty(),
            $name,
            ReflectionParameterCollection::empty(),
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

    public function position(): ByteOffsetRange
    {
        return $this->range;
    }

    public function frame(): Frame
    {
        return $this->frame;
    }

    public function docblock(): DocBlock
    {
        return $this->docblock;
    }

    public function scope(): ReflectionScope
    {
        return $this->scope;
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
        return $this->source;
    }

    public function name(): Name
    {
        return $this->name;
    }
}
