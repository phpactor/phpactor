<?php

namespace Phpactor\DocblockParser\Ast\Tag;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\Token;
use Phpactor\DocblockParser\Ast\TypeNode;
use Phpactor\DocblockParser\Ast\VariableNode;

class VarTag extends TagNode
{
    protected const CHILD_NAMES = [
        'tag',
        'type',
        'variable',
    ];

    public function __construct(
        public Token $tag,
        public ?TypeNode $type,
        public ?VariableNode $variable
    ) {
    }

    public function type(): ?TypeNode
    {
        return $this->type;
    }

    public function variable(): ?VariableNode
    {
        return $this->variable;
    }

    public function name(): ?string
    {
        if (null === $this->variable) {
            return null;
        }

        return $this->variable->name()->toString();
    }
}
