<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\EnumDeclaration;
use Microsoft\PhpParser\Node\Statement\InterfaceDeclaration;
use Microsoft\PhpParser\Node\Statement\TraitDeclaration;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\TypeFactory;

class ClassLikeResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert(
            $node instanceof ClassDeclaration ||
            $node instanceof TraitDeclaration ||
            $node instanceof EnumDeclaration ||
            $node instanceof InterfaceDeclaration
        );

        $context = $context
            ->withSymbolName((string)$node->name->getText((string)$node->getFileContents()))
            ->withSymbolType(Symbol::CLASS_)
            ->withType(
                TypeFactory::fromStringWithReflector(
                    $node->getNamespacedName(),
                    $resolver->reflector(),
                )
            );

        if ($node instanceof ClassDeclaration) {
            foreach ($node->classMembers->classMemberDeclarations as $classMember) {
                $resolver->resolveNode($context, $classMember);
            }
        }

        return $context;
    }
}
