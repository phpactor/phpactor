<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Variable as PhpactorVariable;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class UndefinedVariableProvider implements DiagnosticProvider
{
    public function __construct(private int $suggestionLevensteinDistance = 4)
    {
    }

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof Variable) {
            return [];
        }
        foreach ($frame->locals()->byName($node->getName()) as $variable) {
            if ($variable->wasDefinition()) {
                return [];
            }
        }

        $name = $node->getName();
        yield new UndefinedVariableDiagnostic(
            NodeUtil::byteOffsetRangeForNode($node),
            $name,
            array_filter(array_map(function (PhpactorVariable $var) {
                return $var->name();
            }, $frame->locals()->definitionsOnly()->mostRecent()->toArray()), function (string $candidate) use ($name) {
                return levenshtein($name, $candidate) < $this->suggestionLevensteinDistance;
            })
        );
    }

    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }
}
