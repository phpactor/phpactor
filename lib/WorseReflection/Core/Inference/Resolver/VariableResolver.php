<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Microsoft\PhpParser\Node\Expression\BracedExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Bridge\TolerantParser\Reflection\ReflectionScope as PhpactorReflectionScope;
use Phpactor\WorseReflection\Core\Inference\Context\MemberDeclarationContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\MemberTypeResolver;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\TypeAssertion;
use Phpactor\WorseReflection\Core\Inference\TypeCombinator;
use Phpactor\WorseReflection\Core\Inference\Variable as PhpactorVariable;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class VariableResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof Variable);

        $this->injectDocblockType($resolver, $context->frame(), $node);

        if ($node->getFirstAncestor(PropertyDeclaration::class)) {
            return $this->resolvePropertyVariable($resolver, $node);
        }

        if ($node->name instanceof BracedExpression) {
            return $resolver->resolveNode($context, $node->name->expression);
        }

        $parent = $node->parent;

        // given `$foo::$bar` we check that we are resolving `$bar` and not
        // `$foo` which both have scoped-property-access as a parent, avoiding
        // an infinite loop.
        if (
            $parent instanceof ScopedPropertyAccessExpression &&
            $parent->memberName === $node
        ) {
            $containerType = $resolver->resolveNode($context, $parent->scopeResolutionQualifier);
            $access =  $this->resolveStaticPropertyAccess($resolver, $containerType->type(), $node);
            return $access;
        }

        $variableName = $node->getText();
        $variables = $context->frame()->locals()->byName($variableName);

        // special handling for assignments
        if ($assignment = $node->getFirstAncestor(AssignmentExpression::class)) {
            assert($assignment instanceof AssignmentExpression);
            // if we are dealintg with the right hand side of the assignement
            if ($assignment->leftOperand !== $node) {
                // do not consider the variable being assigned to
                $variables = $variables->not($assignment->getStartPosition());
            }
        }

        $frameVariable = $variables->lessThanOrEqualTo($node->getStartPosition())->lastOrNull();

        $type = new MissingType();
        if ($frameVariable) {
            $type = $frameVariable->type();
        }


        $context = NodeContextFactory::forVariableAt(
            $context->frame(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            $variableName
        )->withTypeAssertion(TypeAssertion::variable(
            $variableName,
            $node->getStartPosition(),
            function (Type $type) {
                return TypeCombinator::subtract(TypeFactory::unionEmpty(), $type);
            },
            fn (Type $type) => TypeCombinator::intersection(TypeFactory::unionEmpty(), $type),
        ))->withType($type);

        $varDocType = $context->frame()->varDocBuffer()->yank($variableName);

        if (null !== $varDocType) {
            $context = $context->withType($varDocType);
            $this->applyVarDoc($context, $context->frame(), $varDocType);
        }

        return $context;
    }

    private function resolvePropertyVariable(NodeContextResolver $resolver, Variable $node): NodeContext
    {
        if (null === $node->getName()) {
            return NodeContext::none();
        }

        $context = NodeContextFactory::create(
            $node->getName(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::PROPERTY,
            ]
        );

        $context = (new MemberTypeResolver($resolver->reflector()))->propertyType(
            NodeUtil::nodeContainerClassLikeType($resolver->reflector(), $node),
            $context,
            $context->symbol()->name()
        );

        return new MemberDeclarationContext($context->symbol(), $context->type(), $context->containerType());
    }

    private function resolveStaticPropertyAccess(NodeContextResolver $resolver, Type $containerType, Variable $node): NodeContext
    {
        $info = NodeContextFactory::create(
            (string)$node->getName(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::PROPERTY,
            ]
        );

        return (new MemberTypeResolver($resolver->reflector()))->propertyType(
            $containerType,
            $info,
            $info->symbol()->name()
        );
    }

    private function applyVarDoc(NodeContext $context, Frame $frame, Type $varDocType): void
    {
        foreach ($frame->locals()->byName($context->symbol()->name())->equalTo($context->symbol()->position()->start()->toInt()) as $existing) {
            assert($existing instanceof PhpactorVariable);
            $frame->locals()->replace($existing, $existing->withType($context->type()));
            return;
        }
        $frame->locals()->set(PhpactorVariable::fromSymbolContext($context));
    }

    private function injectDocblockType(NodeContextResolver $resolver, Frame $frame, Variable $node): Frame
    {
        $scope = new PhpactorReflectionScope($resolver->reflector(), $node);
        $docblockType = $this->injectVariablesFromComment($resolver, $scope, $frame, $node);

        if (null === $docblockType) {
            return $frame;
        }

        if (!$node instanceof Variable) {
            return $frame;
        }

        $token = $node->name;
        if (false === $token instanceof Token) {
            return $frame;
        }

        $name = (string)$token->getText($node->getFileContents());
        $frame->varDocBuffer()->set($name, $docblockType);

        return $frame;
    }

    private function injectVariablesFromComment(NodeContextResolver $resolver, PhpactorReflectionScope $scope, Frame $frame, Node $node): ?Type
    {
        $comment = $node->getLeadingCommentAndWhitespaceText();
        $docblock = $resolver->docblockFactory()->create($comment, $scope);

        if (false === $docblock->isDefined()) {
            return null;
        }

        $vars = $docblock->vars();
        $resolvedTypes = [];

        foreach ($docblock->vars() as $var) {
            $type = $var->type();

            if (empty($var->name())) {
                return $type;
            }

            $frame->varDocBuffer()->set('$' . $var->name(), $type);
        }

        return null;
    }
}
