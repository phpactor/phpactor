<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast\Tag;

use Phpactor\WorseReflection\DocblockParser\Ast\TagNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Token;
use Phpactor\WorseReflection\DocblockParser\Ast\TypeNode;
use Phpactor\WorseReflection\DocblockParser\Ast\VariableNode;

class VarTag extends TagNode
{
    protected const CHILD_NAMES = [
        'tag',
        'type',
        'variable',
    ];

    /**
     * @var ?TypeNode
     */
    public $type;

    /**
     * @var ?VariableNode
     */
    public $variable;
    
    public Token $tag;

    public function __construct(Token $tag, ?TypeNode $type, ?VariableNode $variable)
    {
        $this->type = $type;
        $this->variable = $variable;
        $this->tag = $tag;
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
