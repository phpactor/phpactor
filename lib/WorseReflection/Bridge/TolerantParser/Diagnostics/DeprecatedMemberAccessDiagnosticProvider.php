<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Inference\Context\MemberAccessContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;

class DeprecatedMemberAccessDiagnosticProvider implements DiagnosticProvider
{
    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof MemberAccessExpression && !$node instanceof ScopedPropertyAccessExpression) {
            return;
        }

        $resolved = $resolver->resolveNode($frame, $node);

        if (!$resolved instanceof MemberAccessContext) {
            return;
        }

        $member = $resolved->accessedMember();
        if (!$member->deprecation()->isDefined()) {
            return;
        }

        yield new DeprecatedMemberAccessDiagnostic(
            $resolved->memberNameRange(),
            $member->name(),
            $member->deprecation()->message(),
            $member->memberType(),
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
