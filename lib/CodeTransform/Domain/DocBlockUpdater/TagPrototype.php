<?php

namespace Phpactor\CodeTransform\Domain\DocBlockUpdater;

use Phpactor\DocblockParser\Ast\TagNode;

interface TagPrototype
{
    public function matches(TagNode $tag): bool;
}
