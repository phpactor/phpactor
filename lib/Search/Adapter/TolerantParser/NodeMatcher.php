<?php

namespace Phpactor\Search\Adapter\TolerantParser;

use Microsoft\PhpParser\Node;

interface NodeMatcher
{
    public function isMatching(Node $node1, Node $node2): bool;
}
