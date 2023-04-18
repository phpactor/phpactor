<?php

namespace Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class MissingReturnTypeProvider implements DiagnosticProvider
{
    public function examples(): iterable
    {
        return [];
    }
    public function exit(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        if (!$node instanceof MethodDeclaration) {
            return;
        }

        $methodName = NodeUtil::nameFromTokenOrNode($node, $node->name);

        if (!$methodName) {
            return;
        }

        if ($node->returnTypeList) {
            return;
        }

        $type = $resolver->resolveNode($frame, $node)->containerType();

        if (!$type instanceof ReflectedClassType) {
            return;
        }

        $reflection = $type->reflectionOrNull();

        if (!$reflection) {
            return;
        }

        // if it's an interface we can't determine the return type
        if ($reflection instanceof ReflectionInterface) {
            return;
        }

        $methods = $reflection->methods()->belongingTo($reflection->name())->byName($methodName);

        if (0 === count($methods)) {
            return;
        }

        $method = $methods->first();

        if ($method->isAbstract()) {
            return;
        }

        if ($method->name() === '__construct') {
            return;
        }

        if ($method->name() === '__destruct') {
            return;
        }

        if ($method->type()->isDefined()) {
            return;
        }

        if ($method->docblock()->returnType()->isMixed()) {
            return;
        }

        if ($method->class()->templateMap()->has($method->docblock()->returnType()->__toString())) {
            return;
        }

        $returnType = $frame->returnType();

        yield new MissingReturnTypeDiagnostic(
            $method->nameRange(),
            $reflection->name()->__toString(),
            $methodName,
            $returnType->generalize()->reduce()
        );
    }

    public function enter(NodeContextResolver $resolver, Frame $frame, Node $node): iterable
    {
        return [];
    }
}
