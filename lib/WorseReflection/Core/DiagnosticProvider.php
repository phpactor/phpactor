<?php

namespace Phpactor\WorseReflection\Core;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

interface DiagnosticProvider
{
    /**
     * @return Generator<Diagnostic>
     */
    public function provide(NodeContextResolver $resolver, Frame $frame, Node $node): Generator;
}
