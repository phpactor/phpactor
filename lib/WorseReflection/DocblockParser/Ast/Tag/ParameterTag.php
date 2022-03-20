<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast\Tag;

use Phpactor\WorseReflection\DocblockParser\Ast\TagNode;
use Phpactor\WorseReflection\DocblockParser\Ast\TypeNode;
use Phpactor\WorseReflection\DocblockParser\Ast\ValueNode;
use Phpactor\WorseReflection\DocblockParser\Ast\VariableNode;

class ParameterTag extends TagNode
{
    protected const CHILD_NAMES = [
        'type',
        'name',
        'default',
    ];
    
    public ?TypeNode $type;
    
    public ?VariableNode $name;
    
    public ?ValueNode $default;

    public function __construct(?TypeNode $type, ?VariableNode $name, ?ValueNode $default)
    {
        $this->type = $type;
        $this->name = $name;
        $this->default = $default;
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
