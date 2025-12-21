<?php

namespace Phpactor\DocblockParser\Ast\Tag;

use Phpactor\DocblockParser\Ast\TagNode;
use Phpactor\DocblockParser\Ast\TypeNode;
use Phpactor\DocblockParser\Ast\ValueNode;
use Phpactor\DocblockParser\Ast\VariableNode;

class ParameterTag extends TagNode
{
    protected const CHILD_NAMES = [
        'type',
        'name',
        'default',
    ];

    public function __construct(
        public ?TypeNode $type,
        public ?VariableNode $name,
        public ?ValueNode $default
    ) {
    }

    public function parameterName(): ?string
    {
        if (null === $this->name) {
            return null;
        }

        return $this->name->name()->toString();
    }

    public function type(): ?TypeNode
    {
        return $this->type;
    }

    public function default(): ?ValueNode
    {
        return $this->default;
    }
}
