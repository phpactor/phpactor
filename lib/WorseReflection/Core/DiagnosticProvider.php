<?php

namespace Phpactor\WorseReflection\Core;

use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

interface DiagnosticProvider
{
    /**
     * @return iterable<Diagnostic>
     */
    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable;

    /**
     * @return iterable<Diagnostic>
     */
    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable;

    /**
     * @return iterable<DiagnosticExample>
     */
    public function examples(): iterable;

    public function name(): string;
}
