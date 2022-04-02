<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\PropertyDeclaration;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\MemberTypeResolver;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\SymbolContextResolver;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class VariableResolver implements Resolver
{
    public function resolve(SymbolContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof Variable);

        if ($node->getFirstAncestor(PropertyDeclaration::class)) {
            return $this->resolvePropertyVariable($resolver, $node);
        }

        $name = $node->getText();

        return NodeContextFactory::forVariableAt(
            $frame,
            $node->getStartPosition(),
            $node->getEndPosition(),
            $name
        );
    }

    private function resolvePropertyVariable(SymbolContextResolver $resolver, Variable $node): NodeContext
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
}
