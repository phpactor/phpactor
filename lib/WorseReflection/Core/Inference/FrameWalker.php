<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;

interface FrameWalker
{
    public function canWalk(Node $node): bool;

    public function walk(FrameBuilder $builder, Frame $frame, Node $node): Frame;
}
