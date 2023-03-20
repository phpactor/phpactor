<?php

namespace Phpactor\WorseReflection\Core\Inference;

use Microsoft\PhpParser\Node;

/**
 * Frame walkers can manipulate a frame.
 *
 * Use this extension point to maniputlate types.
 */
interface Walker
{
    /**
     * Return a list of node FQNs that are accepted by this walker or an empty
     * array to accept all nodes.
     *
     * @return class-string[]
     */
    public function nodeFqns(): array;

    public function enter(FrameResolver $resolver, FrameStack $frameStack, Node $node): void;

    public function exit(FrameResolver $resolver, FrameStack $frameStack, Node $node): void;
}
