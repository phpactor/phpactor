<?php

namespace Phpactor\DocblockParser\Ast\Tag;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class PropertyTag extends TagNode
{
    protected const CHILD_NAMES = [
        'tag',
        'type',
        'name',
    ];

    public function __construct(
        public Token $tag,
        public ?TypeNode $type,
        public ?Token $name
    ) {
    }

    public function propertyName(): ?string
    {
        if (null === $this->name) {
            return null;
        }

        return $this->name->toString();
    }
}
