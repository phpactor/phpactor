<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Token;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\GenericMapResolver;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassStringType;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;

class ObjectCreationExpressionResolver implements Resolver
{
    public function __construct(private GenericMapResolver $resolver)
    {
    }

    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof ObjectCreationExpression);

        $className = $node->classTypeDesignator;
        if ($className instanceof Token) {
            return $this->resolveAnonymousClass($resolver, $node);
        }

        $classContext = $resolver->resolveNode($frame, $className);
        $classType = $classContext->type();

        if ($classType instanceof ClassStringType) {
            if ($classType->className() === null) {
                return $classContext->withType(TypeFactory::object());
            }
            $classType = TypeFactory::class($classType->className());
        }

        if ($classType instanceof ClassType) {
            return $classContext->withType($this->resolveClassType($resolver, $frame, $node, $classType));
        }


        return $classContext;
    }

    private function resolveClassType(NodeContextResolver $resolver, Frame $frame, ObjectCreationExpression $node, ClassType $classType): Type
    {
        try {
            $reflection = $resolver->reflector()->reflectClass($classType->name());
        } catch (NotFound) {
            return $classType;
        }
        if (!$reflection->methods()->has('__construct')) {
            return $classType;
        }
        $templateMap = $reflection->docblock()->templateMap();

        if (!count($templateMap)) {
            return $classType;
        }

        $arguments = FunctionArguments::fromList($resolver, $frame, $node->argumentExpressionList);
        $templateMap = $this->resolver->mergeParameters(
            $templateMap,
            $reflection->methods()->get('__construct')->parameters(),
            $arguments
        );
        return new GenericClassType($resolver->reflector(), $classType->name(), $templateMap->toArguments());
    }

    private function resolveAnonymousClass(NodeContextResolver $resolver, Node $node): NodeContext
    {
        return NodeContextFactory::create(
            'testing',
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::CLASS_,
                'type' => TypeFactory::reflectedClass($resolver->reflector(), NodeUtil::nameFromTokenOrNode($node, $node))
            ]
        );
    }
}
