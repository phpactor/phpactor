<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\NodeToTypeConverter;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Reflector\FunctionReflector;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;

class QualifiedNameResolver implements Resolver
{
    private FunctionReflector $reflector;

    private NodeToTypeConverter $nodeTypeConverter;


    public function __construct(FunctionReflector $reflector, NodeToTypeConverter $nodeTypeConverter)
    {
        $this->reflector = $reflector;
        $this->nodeTypeConverter = $nodeTypeConverter;
    }

    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof QualifiedName);

        if ($node->parent instanceof CallExpression) {
            $name = $node->getResolvedName() ?: $node;
            $name = Name::fromString((string) $name);
            $context = NodeContextFactory::create(
                $name->short(),
                $node->getStartPosition(),
                $node->getEndPosition(),
                [
                    'symbol_type' => Symbol::FUNCTION,
                ]
            );

            try {
                $function = $this->reflector->reflectFunction($name);
            } catch (NotFound $exception) {
                return $context->withIssue($exception->getMessage());
            }

            return $context->withType($function->inferredType());
        }

        return NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'type' => $this->resolveType($node),
                'symbol_type' => Symbol::CLASS_,
            ]
        );
    }

    private function resolveType(QualifiedName $node): Type
    {
        $text = $node->getText();

        // magic constants
        if ($text === '__DIR__') {
            return TypeFactory::stringLiteral(dirname($node->getUri()));
        }

        return $this->nodeTypeConverter->resolve($node);
    }
}
