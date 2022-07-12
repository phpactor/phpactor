<?php

namespace Phpactor\DocblockParser\Ast;

class ConditionalNode extends TypeNode
{
    protected const CHILD_NAMES = [
        'variable',
        'is',
        'isType',
        'question',
        'left',
        'colon',
        'right',
    ];

    public VariableNode $variable;

    public ?Token $is;

    public ?TypeNode $isType;

    public ?Token $question;

    public ?TypeNode $left;

    public ?Token $colon;

    public ?TypeNode $right;

    public function __construct(
        VariableNode $variable,
        ?Token $is = null,
        ?TypeNode $isType = null,
        ?Token $question = null,
        ?TypeNode $left = null,
        ?Token $colon = null,
        ?TypeNode $right = null
    ) {
        $this->variable = $variable;
        $this->is = $is;
        $this->isType = $isType;
        $this->question = $question;
        $this->left = $left;
        $this->colon = $colon;
        $this->right = $right;
    }
}
