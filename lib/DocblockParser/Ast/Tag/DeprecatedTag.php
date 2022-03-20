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
    
    public ?TextNode $text;
    
    public Token $token;

    public function __construct(Token $token, ?TextNode $text)
    {
        $this->text = $text;
        $this->token = $token;
    }

    public function text(): ?string
    {
        if ($this->text) {
            return $this->text->toString();
        }

        return null;
    }
}
