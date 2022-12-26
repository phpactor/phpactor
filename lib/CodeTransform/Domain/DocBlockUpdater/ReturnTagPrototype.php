<?php

namespace Phpactor\CodeTransform\Domain\DocBlockUpdater;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\Tag\ReturnTag;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\StringType;

class ReturnTagPrototype implements TagPrototype
{
    public function __construct(public Type $type)
    {
    }

    public function matches(TagNode $tag): bool
    {
        return $tag instanceof ReturnTag;
    }
}
