<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast\Tag;

use Phpactor\WorseReflection\DocblockParser\Ast\TagNode;
use Phpactor\WorseReflection\DocblockParser\Ast\TextNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Token;
use Phpactor\WorseReflection\DocblockParser\Ast\TypeNode;
use Phpactor\WorseReflection\DocblockParser\Ast\VariableNode;

class ParamTag extends TagNode
{
    protected const CHILD_NAMES = [
        'tag',
        'type',
        'variable',
        'text',
    ];

    /**
     * @var ?TypeNode
     */
    public $type;

    /**
     * @var ?VariableNode
     */
    public $variable;
    
    public ?TextNode $text;
    
    public Token $tag;

    public function __construct(Token $tag, ?TypeNode $type, ?VariableNode $variable, ?TextNode $text = null)
    {
        $this->type = $type;
        $this->variable = $variable;
        $this->text = $text;
        $this->tag = $tag;
    }

    public function paramName(): ?string
    {
        if (null === $this->variable) {
            return null;
        }

        return $this->variable->name()->toString();
    }

    public function type(): ?TypeNode
    {
        return $this->type;
    }

    public function variable(): ?VariableNode
    {
        return $this->variable;
    }

    public function text(): ?TextNode
    {
        return $this->text;
    }
}
