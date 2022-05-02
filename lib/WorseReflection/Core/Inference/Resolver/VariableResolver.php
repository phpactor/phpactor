<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\MemberTypeResolver;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\TypeAssertion;
use Phpactor\WorseReflection\Core\Inference\TypeCombinator;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class VariableResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof Variable);

        if ($node->getFirstAncestor(PropertyDeclaration::class)) {
            return $this->resolvePropertyVariable($resolver, $node);
        }

        $parent = $node->parent;

        // given `$foo::$bar` we check that we are resolving `$bar` and not
        // `$foo` which both have scoped-property-access as a parent, avoiding
        // an infinite loop.
        if (
            $parent instanceof ScopedPropertyAccessExpression &&
            $parent->memberName === $node
        ) {
            $containerType = $resolver->resolveNode($frame, $parent->scopeResolutionQualifier);
            $access =  $this->resolveStaticPropertyAccess($resolver, $containerType->type(), $node);
            return $access;
        }

        $variableName = $node->getText();

        return NodeContextFactory::forVariableAt(
            $frame,
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
        ));
    }

    private function resolvePropertyVariable(NodeContextResolver $resolver, Variable $node): NodeContext
    {
        $info = NodeContextFactory::create(
            $node->getName(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::PROPERTY,
            ]
        );

        return (new MemberTypeResolver($resolver->reflector()))->propertyType(
            NodeUtil::nodeContainerClassLikeType($resolver->reflector(), $node),
            $info,
            $info->symbol()->name()
        );
    }

    private function resolveStaticPropertyAccess(NodeContextResolver $resolver, Type $containerType, Variable $node): NodeContext
    {
        $info = NodeContextFactory::create(
            $node->getName(),
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
}
