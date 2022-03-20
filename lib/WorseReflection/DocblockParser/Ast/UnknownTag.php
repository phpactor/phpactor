<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast;

class UnknownTag extends TagNode
{
    public Token $name;

    public function __construct(Token $name)
    {
        $this->name = $name;
    }
}
