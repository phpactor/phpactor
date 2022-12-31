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
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\ClosureType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
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

        // do not try it for overriden methods
        if ($method->original()->declaringClass()->name() != $class->name()) {
            return;
        }

        $docblock = $method->docblock();
        $docblockParams = $docblock->params();
        $missingParams = [];

        foreach ($method->parameters() as $parameter) {
            $type = $parameter->type();
            $type = $this->upcastType($type, $resolver);
            $type = $type->toLocalType($method->scope());

            if ($docblockParams->has($parameter->name())) {
                continue;
            }

            if ($type instanceof ArrayType) {
                $type = new ArrayType(TypeFactory::int(), TypeFactory::mixed());
            }

            $type = $type->map(fn (Type $type) => $type instanceof MissingType ? new MixedType() : $type);

            if ($type->__toString() === $parameter->type()->toPhpString()) {
                continue;
            }

            yield new MissingDocblockParamDiagnostic(
                ByteOffsetRange::fromInts(
                    $parameter->position()->start(),
                    $parameter->position()->end()
                ),
                sprintf(
                    'Method "%s" is missing @param $%s',
                    $methodName,
                    $parameter->name(),
                ),
                DiagnosticSeverity::WARNING(),
                $class->name()->__toString(),
                $methodName,
                $parameter->name(),
                $type,
            );
        }
    }

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }

    private function upcastType(Type $type, NodeContextResolver $resolver): Type
    {
        if (!$type instanceof ReflectedClassType) {
            return $type;
        }

        if ($type->name()->__toString() === 'Closure') {
            return new ClosureType($resolver->reflector(), [], TypeFactory::void());
        }

        return $type->upcastToGeneric();
    }
}
