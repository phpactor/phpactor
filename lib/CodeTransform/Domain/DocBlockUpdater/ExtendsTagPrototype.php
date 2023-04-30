<?php

namespace Phpactor\CodeTransform\Domain\DocBlockUpdater;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\Tag\ExtendsTag;
use Phpactor\WorseReflection\Core\Type;

class ExtendsTagPrototype implements TagPrototype
{
    public function __construct(public Type $type)
    {
    }

    public function matches(TagNode $tag): bool
    {
        return $tag instanceof ExtendsTag && $tag->toString() === $this->type->__toString();
    }

    public function endOffsetFor(TagNode $tag): int
    {
        assert($tag instanceof ExtendsTag);
        return $tag->end();
    }
}
