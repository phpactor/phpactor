<?php

namespace Phpactor\CodeTransform\Domain\DocBlockUpdater;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\Tag\ParamTag;
use Phpactor\WorseReflection\Core\Type;

class ParamTagPrototype implements TagPrototype
{
    public function __construct(public string $name, public Type $type)
    {
    }

    public function matches(TagNode $tag): bool
    {
        return $tag instanceof ParamTag && ltrim($tag->paramName(), '$') === $this->name;
    }

    public function endOffsetFor(TagNode $tag): int
    {
        assert($tag instanceof ParamTag);
        return $tag->variable ? $tag->variable->end() : $tag->end();
    }
}
