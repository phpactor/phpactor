<?php

namespace Phpactor\WorseReflection\Core\Virtual;

use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\TextDocument\TextDocumentBuilder;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\DocBlock\PlainDocblock;
use Phpactor\WorseReflection\Core\Inference\Frame\ConcreteFrame;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\NodeText;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionParameterCollection;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunction;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

final class VirtualReflectionFunction implements ReflectionFunction
{
    public function __construct(
        private readonly ByteOffsetRange $range,
        private readonly NodeText $body,
        private readonly Frame $frame,
        private readonly DocBlock $docblock,
        private readonly ReflectionScope $scope,
        private readonly Type $inferredType,
        private readonly Type $type,
        private readonly TextDocument $source,
        private readonly Name $name,
        private readonly ReflectionParameterCollection $parameters,
    ) {
    }

    public static function empty(
        Name $name,
        ByteOffsetRange $range
    ): self {
        return new self(
            $range,
            NodeText::fromString(''),
            new ConcreteFrame(),
            new PlainDocblock(),
            new DummyReflectionScope(),
            TypeFactory::undefined(),
            TypeFactory::undefined(),
            TextDocumentBuilder::empty(),
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

    public function sourceCode(): TextDocument
    {
        return $this->source;
    }

    public function name(): Name
    {
        return $this->name;
    }
}
