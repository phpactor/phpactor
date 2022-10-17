<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\GenericMapResolver;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Type\ClassType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;

class ObjectCreationExpressionResolver implements Resolver
{
    private GenericMapResolver $resolver;

    public function __construct(GenericMapResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof ObjectCreationExpression);
        if (false === $node->classTypeDesignator instanceof Node) {
            throw new CouldNotResolveNode(sprintf('Could not create object from "%s"', get_class($node)));
        }

        $arguments = FunctionArguments::fromList($resolver, $frame, $node->argumentExpressionList);

        $classContext = $resolver->resolveNode($frame, $node->classTypeDesignator);
        $classType = $classContext->type();

        if ($classType instanceof ClassType) {
            try {
                $reflection = $resolver->reflector()->reflectClass($classType->name());
            } catch (NotFound $notFound) {
                return $classContext;
            }
            if (!$reflection->methods()->has('__construct')) {
                return $classContext;
            }
            $templateMap = $reflection->docblock()->templateMap();
            $templateMap = $this->resolver->mergeParameters($templateMap, $reflection->methods()->get('__construct')->parameters(), $arguments);
            $classContext = $classContext->withType(new GenericClassType($resolver->reflector(), $classType->name(), $templateMap->toArguments()));
        }


        return $classContext;
    }
}
