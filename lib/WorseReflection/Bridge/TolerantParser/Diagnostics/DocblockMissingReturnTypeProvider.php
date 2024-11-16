<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use PHPUnit\Framework\Assert;
use Phpactor\WorseReflection\Core\DiagnosticExample;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\Diagnostics;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

/**
 * Report when a method has a return type should be
 * augmented by a docblock tag
 */
class DocblockMissingReturnTypeProvider implements DiagnosticProvider
{
    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof MethodDeclaration) {
            return;
        }

        if (!$node->name) {
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

        $docblockType = $method->docblock()->returnType();
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

        if ($claimedReturnType->isDefined()
            && !$claimedReturnType->isClass()
            && !$claimedReturnType->isArray()
            && !$claimedReturnType->isClosure()
            && !$claimedReturnType->isIterable()
        ) {
            return;
        }


        if ($actualReturnType->isClosure()) {
            yield new DocblockMissingReturnTypeDiagnostic(
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

        if ($actualReturnType->isMixed() && ($claimedReturnType->isArray() || $claimedReturnType->isIterable())) {
            return;
        }

        // the docblock matches the generalized return type
        // it's OK
        if ($claimedReturnType->equals($actualReturnType)) {
            return;
        }

        yield new DocblockMissingReturnTypeDiagnostic(
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

    public function examples(): iterable
    {
        yield new DiagnosticExample(
            title: 'method without return type',
            source: <<<'PHP'
                <?php

                class Foobar
                {
                    public function foo() {
                        return 'foobar';
                    }
                }
                PHP,
            valid: false,
            assertion: function (Diagnostics $diagnostics): void {
                Assert::assertCount(1, $diagnostics);
            }
        );
    }
}
