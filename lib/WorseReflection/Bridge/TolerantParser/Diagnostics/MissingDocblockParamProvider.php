<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\DiagnosticSeverity;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\IterableType;
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
        $params = $docblock->params();
        $missingParams = [];

        foreach ($method->parameters() as $parameter) {
            if (!$parameter->type() instanceof IterableType) {
                continue;
            }

            if ($params->has($parameter->name())) {
                continue;
            }

            yield new MissingDocblockParamDiagnostic(
                ByteOffsetRange::fromInts(
                    $parameter->position()->start(),
                    $parameter->position()->end()
                ),
                sprintf(
                    'Method "%s" is missing docblock param: $%s of type %s',
                    $methodName,
                    $parameter->name(),
                    $parameter->type(),
                ),
                DiagnosticSeverity::WARNING(),
                $class->name()->__toString(),
                $methodName
            );
        }
    }

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }
}
