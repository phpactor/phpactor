<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\WorseReflection\Core\Inference\Context\MemberDeclarationContext;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class MethodDeclarationResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof MethodDeclaration);

        $classNode = NodeUtil::nodeContainerClassLikeDeclaration($node);
        if (null === $classNode) {
            return $context;
        }
        $classSymbolContext = $resolver->resolveNode($context, $classNode);

        $resolver->resolveNode($context, $node->compoundStatementOrSemicolon);

        return $context->replace(new MemberDeclarationContext(
            Symbol::fromTypeNameAndPosition(
                Symbol::METHOD,
                (string)$node->name?->getText($node->getFileContents()),
                ByteOffsetRange::fromInts(
                    $node->name?->getStartPosition() ?? $node->getStartPosition(),
                    $node->name?->getEndPosition() ?? $node->getEndPosition()
                )
            ),
            TypeFactory::unknown(),
            $classSymbolContext->type()
        ));
    }
}
