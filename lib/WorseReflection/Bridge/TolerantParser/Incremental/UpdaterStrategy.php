<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Incremental;

use Microsoft\PhpParser\Node;
use Phpactor\TextDocument\TextEdit;

interface UpdaterStrategy
{
    public function apply(Node $node, TextEdit $edit): OperationResult;
}
