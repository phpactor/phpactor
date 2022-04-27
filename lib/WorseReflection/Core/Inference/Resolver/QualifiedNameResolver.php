<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\FunctionStubRegistry;
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

    private FunctionStubRegistry $registry;

    public function __construct(
        FunctionReflector $reflector,
        FunctionStubRegistry $registry,
        NodeToTypeConverter $nodeTypeConverter
    ) {
        $this->reflector = $reflector;
        $this->nodeTypeConverter = $nodeTypeConverter;
        $this->registry = $registry;
    }

    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof QualifiedName);

        $parent = $node->parent;
        if ($parent instanceof CallExpression) {
            $name = $node->getResolvedName();
            if (null === $name) {
                $name = $node->getNamespacedName();
            }
            $name = Name::fromString((string) $name);
            $context = NodeContextFactory::create(
                $name->full(),
                $node->getStartPosition(),
                $node->getEndPosition(),
                [
                    'symbol_type' => Symbol::FUNCTION,
                ]
            );

            $stub = $this->registry->get($name->short());

            if ($stub && $parent->argumentExpressionList) {
                $arguments = FunctionArguments::fromList(
                    $resolver,
                    $frame,
                    $parent->argumentExpressionList
                );
                return $stub->resolve($context, $arguments);
            }

            try {
                $function = $this->reflector->reflectFunction($name);
            } catch (NotFound $exception) {
                return $context->withIssue($exception->getMessage());
            }

            // the function may have been resolved to a global, so create
            // the context again with the potentially shorter name
            $context = NodeContextFactory::create(
                $function->name()->__toString(),
                $node->getStartPosition(),
                $node->getEndPosition(),
                [
                    'symbol_type' => Symbol::FUNCTION,
                ]
            );

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
            // TODO: [TP] tolerant parser `getUri` returns NULL or string but only declares NULL
            if (!$node->getRoot()->uri) {
                return TypeFactory::string();
            }
            return TypeFactory::stringLiteral(dirname($node->getUri()));
        }

        return $this->nodeTypeConverter->resolve($node);
    }
}
