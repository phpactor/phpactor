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
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

class QualifiedNameResolver implements Resolver
{
    private Reflector $reflector;

    private NodeToTypeConverter $nodeTypeConverter;

    private FunctionStubRegistry $registry;

    public function __construct(
        Reflector $reflector,
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

            if ($stub) {
                $arguments = FunctionArguments::fromList(
                    $resolver,
                    $frame,
                    $parent->argumentExpressionList
                );
                return $stub->resolve($frame, $context, $arguments);
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

            return $context->withType($function->inferredType()->reduce());
        }


        return $this->resolveContext($node);
    }

    private function resolveContext(QualifiedName $node): NodeContext
    {
        $context = NodeContextFactory::create(
            $node->getText(),
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::CLASS_
            ]
        );

        $text = $node->getText();

        // magic constants
        if ($text === '__DIR__') {
            // TODO: [TP] tolerant parser `getUri` returns NULL or string but only declares NULL
            if (!$node->getRoot()->uri) {
                return $context->withType(TypeFactory::string());
            }
            return $context->withType(TypeFactory::stringLiteral(dirname($node->getUri())));
        }

        $type = $this->nodeTypeConverter->resolve($node);

        if ($type instanceof ReflectedClassType) {
            try {
                // fast but inaccurate check to see if class exists
                $this->reflector->sourceCodeForClassLike($type->name());
                // accurate check to see if class exists
                $this->reflector->reflectClassLike($type->name());
                return $context->withType($type);
            } catch (NotFound $notFound) {
                // resolve the name of the potential constant
                [$_, $_, $constImportTable] = $node->getImportTablesForCurrentScope();
                if ($resolved = NodeUtil::resolveNameFromImportTable($node, $constImportTable)) {
                    $name = $resolved->__toString();
                } else {
                    $name = $type->name()->full();
                }
                try {
                    // fast but inaccurate check to see if constant exists
                    $sourceCode = $this->reflector->sourceCodeForConstant($name);
                    // accurate check to see if constant exists
                    $constant = $this->reflector->reflectConstant($name);
                    return $context
                        ->withSymbolName($constant->name()->full())
                        ->withType($constant->type())
                        ->withSymbolType(Symbol::DECLARED_CONSTANT);
                } catch (NotFound $e) {
                }
            }
        }

        return $context->withType($type);
    }
}
