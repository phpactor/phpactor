<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Generator;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class MissingMethods implements DiagnosticProvider
{
    public function provide(NodeContextResolver $resolver, Node $node): Generator
    {
        dd('asd');
    }
}
