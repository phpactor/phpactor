<?php

namespace Phpactor\WorseReflection\DocblockParser\Ast;

abstract class ValueNode extends Node
{
    /**
     * @return mixed
     */
    abstract public function value();
}
