<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Inference\Context\MethodCallContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class DeprecatedMemberAccessDiagnosticProvider implements DiagnosticProvider
{
    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof CallExpression) {
            return;
        }
        $resolved = $resolver->resolveNode($frame, $node);

        if (!$resolved instanceof MethodCallContext) {
            return;
        }

        $method = $resolved->reflectionMethod();
        if (!$method->deprecation()->isDefined()) {
            return;
        }

        yield new DeprecatedMemberAccessDiagnostic(
            $resolved->memberNameRange(),
            $method->name(),
            $method->deprecation()->message(),
        );
    }

    /**
     * @deprecated This is fobar
     */
    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }
}
