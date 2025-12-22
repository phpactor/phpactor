<?php

namespace Phpactor\DocblockParser\Ast\Tag;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\TextNode;
use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class ThrowsTag extends TagNode
{
    protected const CHILD_NAMES = [
        'tag',
        'exceptionClass',
        'text',
    ];

    public function __construct(
        public Token $tag,
        public ?TypeNode $exceptionClass,
        public ?TextNode $text
    ) {
    }
}
