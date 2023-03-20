<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\WorseReflection\Core\Inference\Context\MemberDeclarationContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class MethodDeclarationResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof MethodDeclaration);


        $classNode = NodeUtil::nodeContainerClassLikeDeclaration($node);
        $classSymbolContext = $resolver->resolveNode($frame, $classNode);

        return new MemberDeclarationContext(
            Symbol::fromTypeNameAndPosition(
                Symbol::METHOD,
                (string)$node->name->getText($node->getFileContents()),
                ByteOffsetRange::fromInts(
                    $node->name->getStartPosition(),
                    $node->name->getEndPosition()
                )
            ),
            TypeFactory::unknown(),
            $classSymbolContext->type()
        );
    }
}
