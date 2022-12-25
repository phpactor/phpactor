<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class MissingDocblockParamProvider implements DiagnosticProvider
{
    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof MethodDeclaration) {
            return;
        }

        $declaration = NodeUtil::nodeContainerClassLikeDeclaration($node);

        if (null === $declaration) {
            return;
        }

        try {
            $class = $resolver->reflector()->reflectClassLike($declaration->getNamespacedName()->__toString());
            $methodName = $node->name->getText($node->getFileContents());
            if (!is_string($methodName)) {
                return;
            }
            $method = $class->methods()->get($methodName);
        } catch (NotFound) {
            return;
        }

        $docblock = $method->docblock();
        $docblockType = $docblock->returnType();
        $actualReturnType = $frame->returnType()->generalize();
        $claimedReturnType = $method->inferredType();
        $phpReturnType = $method->type();

        // if there is already a return type, ignore. phpactor's guess
        // will currently likely be wrong often.
        if ($method->docblock()->returnType()->isDefined()) {
            return;
        }

        // do not try it for overriden methods
        if ($method->original()->declaringClass()->name() != $class->name()) {
            return;
        }

        if ($method->name() === '__construct') {
            return;
        }

        // it's void
        if (false === $actualReturnType->isDefined()) {
            return;
        }

        if (
            $claimedReturnType->isDefined() && !$claimedReturnType->isClass() && !$claimedReturnType->isArray() && !$claimedReturnType->isClosure()
        ) {
            return;
        }

        if ($actualReturnType->isClosure()) {
            yield new MissingDocblockReturnTypeDiagnostic(
                $method->nameRange(),
                sprintf(
                    'Method "%s" is missing docblock return type: %s',
                    $methodName,
                    $actualReturnType->__toString(),
                ),
                DiagnosticSeverity::WARNING(),
                $class->name()->__toString(),
                $methodName,
                $actualReturnType->__toString(),
            );
            return;
        }

        if ($claimedReturnType->isClass() && !$actualReturnType instanceof GenericClassType) {
            return;
        }

        if ($claimedReturnType->isArray() && $actualReturnType->isMixed()) {
            return;
        }

        // the docblock matches the generalized return type
        // it's OK
        if ($claimedReturnType->equals($actualReturnType)) {
            return;
        }

        yield new MissingDocblockReturnTypeDiagnostic(
            $method->nameRange(),
            sprintf(
                'Method "%s" is missing docblock return type: %s',
                $methodName,
                $actualReturnType->__toString(),
            ),
            DiagnosticSeverity::WARNING(),
            $class->name()->__toString(),
            $methodName,
            $actualReturnType->__toString(),
        );
    }

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }
}
