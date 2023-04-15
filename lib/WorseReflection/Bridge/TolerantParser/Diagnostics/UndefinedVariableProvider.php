<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Variable as PhpactorVariable;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class UndefinedVariableProvider implements DiagnosticProvider
{
    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof Variable) {
            return [];
        }
        foreach ($frame->locals()->byName($node->getName()) as $variable) {
            if ($variable->wasAssigned()) {
                return [];
            }
        }

        yield new UndefinedVariableDiagnostic(
            NodeUtil::byteOffsetRangeForNode($node),
            $node->getName(),
            array_map(function (PhpactorVariable $var) {
                return $var->name();
            }, $frame->locals()->mostRecent()->toArray())
        );
    }

    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }
}
