<?php

namespace Phpactor\WorseReflection\Core;

use Microsoft\PhpParser\Node;

interface NodeContextVisitor
{
    /**
     * @return list<class-string<Node>>
     */
    public function fqns(): array;
}
