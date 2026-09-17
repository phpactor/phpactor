<?php

namespace Phpactor\Extension\LanguageServerCallHierarchy\Inference;

use Microsoft\PhpParser\Node;

/**
 * A single call site collected by OutgoingCallsWalker.
 */
final class Call
{
    public function __construct(
        public Node $node,
        public string $name,
        public string $kind,
        public int $offset,
    ) {
    }
}
