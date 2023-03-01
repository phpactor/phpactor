<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Inference\Context\ClassLikeContext;
use Phpactor\WorseReflection\Core\Inference\Context\FunctionCallContext;
use Phpactor\WorseReflection\Core\Inference\Context\MemberAccessContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMember;

class DeprecatedUsageDiagnosticProvider implements DiagnosticProvider
{
    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof MemberAccessExpression && !$node instanceof ScopedPropertyAccessExpression && !$node instanceof QualifiedName) {
            return;
        }

        $resolved = $resolver->resolveNode($frame, $node);

        if ($resolved instanceof MemberAccessContext) {
            yield from $this->memberAccessDiagnostics($resolved);
        }
        if ($resolved instanceof ClassLikeContext) {
            yield from $this->classLikeDiagnostics($resolved);
        }
        if ($resolved instanceof FunctionCallContext) {
            yield from $this->functionDiagnostics($resolved);
        }
    }

    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }

    /**
     * @param MemberAccessContext<ReflectionMember> $resolved
     * @return Generator<DeprecatedMemberAccessDiagnostic>
     */
    private function memberAccessDiagnostics(MemberAccessContext $resolved): Generator
    {
        $member = $resolved->accessedMember();
        if (!$member->deprecation()->isDefined()) {
            return;
        }

        yield new DeprecatedUsageDiagnostic(
            $resolved->memberNameRange(),
            $member->name(),
            $member->deprecation()->message(),
            $member->memberType(),
        );
    }
    /**
     * @return Generator<DeprecatedMemberAccessDiagnostic>
     */
    private function classLikeDiagnostics(ClassLikeContext $resolved): Generator
    {
        $reflectionClass = $resolved->classLike();
        if (!$reflectionClass->deprecation()->isDefined()) {
            return;
        }

        yield new DeprecatedUsageDiagnostic(
            $resolved->range(),
            $reflectionClass->name(),
            $reflectionClass->deprecation()->message(),
            $reflectionClass->classLikeType(),
        );
    }
    /**
     * @return Generator<DeprecatedMemberAccessDiagnostic>
     */
    private function functionDiagnostics(FunctionCallContext $resolved): Generator
    {
        $reflectionFunction = $resolved->function();
        if (!$reflectionFunction->docblock()->deprecation()->isDefined()) {
            return;
        }

        yield new DeprecatedUsageDiagnostic(
            $resolved->range(),
            $reflectionFunction->name(),
            $reflectionFunction->docblock()->deprecation()->message(),
            'function',
        );
    }
}
