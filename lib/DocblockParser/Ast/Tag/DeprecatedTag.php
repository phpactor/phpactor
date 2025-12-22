<?php

namespace Phpactor\DocblockParser\Ast\Tag;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\TextNode;
use Phpactor\DocblockParser\Ast\Token;

class DeprecatedTag extends TagNode
{
    public const CHILD_NAMES = [
        'token',
        'text',
    ];

    public function __construct(
        public Token $token,
        public ?TextNode $text
    ) {
    }

    public function text(): ?string
    {
        if ($this->text) {
            return $this->text->toString();
        }

        return null;
    }
}
