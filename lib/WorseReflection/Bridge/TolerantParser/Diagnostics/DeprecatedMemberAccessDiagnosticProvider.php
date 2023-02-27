<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class DeprecatedMemberAccessDiagnosticProvider implements DiagnosticProvider
{
    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if ($node instanceof CallExpression) {
            $resolved = $resolver->resolveNode($frame, $node);
            dump($resolved::class);
        }
        return [];
    }

    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }
}
