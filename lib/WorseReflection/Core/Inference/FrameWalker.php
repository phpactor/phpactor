<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;

interface FrameWalker
{
    /**
     * @return class-string[]
     */
    public function nodeFqns(): array;

    public function walk(FrameResolver $resolver, Frame $frame, Node $node): Frame;
}
