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
        ?Token $is,
        ?TypeNode $isType,
        ?Token $question,
        ?TypeNode $left,
        ?Token $colon,
        ?TypeNode $right
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
