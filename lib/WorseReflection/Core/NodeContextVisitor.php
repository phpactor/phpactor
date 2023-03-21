<?php

namespace Phpactor\WorseReflection\Core;

use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\NodeContext;

interface NodeContextVisitor
{
    /**
     * @return list<class-string<Node>>
     */
    public function fqns(): array;

    public function visit(NodeContext $context): NodeContext;
}
