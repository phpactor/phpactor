<?php

namespace Phpactor\Extension\PHPUnit\FrameWalker;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Inference\Walker;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassStringType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\StringLiteralType;

class AssertInstanceOfWalker implements Walker
{
    public function nodeFqns(): array
    {
        return [
            ScopedPropertyAccessExpression::class,
            MemberAccessExpression::class,
        ];
    }

    public function enter(FrameResolver $resolver, FrameStack $frameStack, Node $node): void
    {
        return;
    }

    public function exit(FrameResolver $resolver, FrameStack $frameStack, Node $node): void
    {
        if (!$this->canWalk($node)) {
            return;
        }

        $callExpression = $node->parent;
        if (!$callExpression instanceof CallExpression) {
            return;
        }

        $args = FunctionArguments::fromList(
            $resolver->resolver(),
            $frameStack->current(),
            $callExpression->argumentExpressionList
        );

        if (count($args) < 2) {
            return;
        }

        $type = $args->at(0)->type();

        if ($type instanceof StringLiteralType) {
            $type = TypeFactory::reflectedClass($resolver->reflector(), $type->value());
        }

        if ($type instanceof ClassStringType) {
            $type = TypeFactory::reflectedClass($resolver->reflector(), $type->className()?->__toString());
        }

        if (!$type instanceof ClassType) {
            return;
        }

        $var = $args->at(1);

        $frame->locals()->set(Variable::fromSymbolContext($var->withType($type)));

        return;
    }

    private function canWalk(Node $node): bool
    {
        if ($node instanceof ScopedPropertyAccessExpression) {
            $scopeResolutionQualifier = $node->scopeResolutionQualifier;

            if (!$scopeResolutionQualifier instanceof QualifiedName) {
                return false;
            }

            $resolvedName = $scopeResolutionQualifier->getResolvedName();
            if ((string) $resolvedName !== 'PHPUnit\Framework\Assert') {
                return false;
            }

            return true;
        }

        if ($node instanceof MemberAccessExpression) {
            $memberName = $node->memberName;

            if (!$memberName instanceof Token) {
                return false;
            }

            // we havn't got the facility to check if we are extending the TestCase
            // here, so just assume that any method named this way is belonging to
            // PHPUnit
            if ('assertInstanceOf' === $memberName->getText($node->getFileContents())) {
                return true;
            }
        }

        return false;
    }
}
