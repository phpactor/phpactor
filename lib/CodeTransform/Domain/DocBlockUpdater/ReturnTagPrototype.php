<?php

namespace Phpactor\CodeTransform\Domain\DocBlockUpdater;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\Tag\ReturnTag;
use Phpactor\WorseReflection\Core\Type;

class ReturnTagPrototype implements TagPrototype
{
    public function __construct(public Type $type)
    {
    }

    public function matches(TagNode $tag): bool
    {
        return $tag instanceof ReturnTag;
    }

    public function endOffsetFor(TagNode $tag): int
    {
        assert($tag instanceof ReturnTag);
        return $tag->type() ? $tag->type()->end() : $tag->end();
    }
}
