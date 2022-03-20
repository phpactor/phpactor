<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast\Tag;

use Phpactor\WorseReflection\DocblockParser\Ast\TagNode;
use Phpactor\WorseReflection\DocblockParser\Ast\TextNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Token;

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
