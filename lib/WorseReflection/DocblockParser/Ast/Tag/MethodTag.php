<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast\Tag;

use Phpactor\WorseReflection\DocblockParser\Ast\ParameterList;
use Phpactor\WorseReflection\DocblockParser\Ast\TagNode;
use Phpactor\WorseReflection\DocblockParser\Ast\TextNode;
use Phpactor\WorseReflection\DocblockParser\Ast\Token;
use Phpactor\WorseReflection\DocblockParser\Ast\TypeNode;

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
    
    public ?TypeNode $type;
    
    public ?Token $name;
    
    public ?Token $static;
    
    public ?ParameterList $parameters;
    
    public ?TextNode $text;
    
    public ?Token $parenOpen;
    
    public ?Token $parenClose;
    
    public ?Token $tag;

    public function __construct(
        ?Token $tag,
        ?TypeNode $type,
        ?Token $name,
        ?Token $static,
        ?Token $parenOpen,
        ?ParameterList $parameters,
        ?Token $parenClose,
        ?TextNode $text
    ) {
        $this->type = $type;
        $this->name = $name;
        $this->static = $static;
        $this->parameters = $parameters;
        $this->text = $text;
        $this->parenOpen = $parenOpen;
        $this->parenClose = $parenClose;
        $this->tag = $tag;
    }

    public function methodName(): ?string
    {
        if (null === $this->name) {
            return null;
        }

        return $this->name->toString();
    }
}
