<?php

namespace Phpactor\DocblockParser\Ast\Tag;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;

class TemplateTag extends TagNode
{
    protected const CHILD_NAMES = [
        'tag',
        'placeholder',
        'constraint',
        'type'
    ];

    public function __construct(
        public Token $tag,
        public ?Token $placeholder = null,
        public ?Token $constraint = null,
        public ?TypeNode $type = null
    ) {
    }

    public function placeholder(): string
    {
        return $this->placeholder ? $this->placeholder->toString() : '';
    }
}
