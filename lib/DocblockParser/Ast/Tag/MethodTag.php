<?php

namespace Phpactor\DocblockParser\Ast\Tag;

use Phpactor\DocblockParser\Ast\ParameterList;
use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\TextNode;
use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class MethodTag extends TagNode
{
    public const CHILD_NAMES = [
        'tag',
        'static',
        'type',
        'name',
        'parenOpen',
        'parameters',
        'parenClose',
        'text'
    ];

    public function __construct(
        public ?Token $tag,
        public ?TypeNode $type,
        public ?Token $name,
        public ?Token $static,
        public ?Token $parenOpen,
        public ?ParameterList $parameters,
        public ?Token $parenClose,
        public ?TextNode $text
    ) {
    }

    public function methodName(): ?string
    {
        if (null === $this->name) {
            return null;
        }

        return $this->name->toString();
    }
}
