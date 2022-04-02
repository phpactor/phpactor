<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;

interface FrameWalker
{
    /**
     * @return ?class-string
     */
    public function nodeFqn(): ?string;

    public function canWalk(Node $node): bool;

    public function walk(FrameResolver $resolver, Frame $frame, Node $node): Frame;
}
