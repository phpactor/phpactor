<?php

namespace Phpactor\DocblockParser\Ast;

abstract class ValueNode extends Node
{
    /**
     * @return mixed
     */
    abstract public function value();
}
