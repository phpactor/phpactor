<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\TextDocument\ByteOffsetRange;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Context\ClassLikeContext;
use Phpactor\WorseReflection\Core\Inference\Context\FunctionCallContext;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FunctionArguments;
use Phpactor\WorseReflection\Core\Inference\FunctionStubRegistry;
use Phpactor\WorseReflection\Core\Inference\NodeToTypeConverter;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Variable;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Core\Virtual\VirtualReflectionFunction;
use Phpactor\WorseReflection\Reflector;

class QualifiedNameResolver implements Resolver
{
    public function __construct(
        private Reflector $reflector,
        private FunctionStubRegistry $registry,
        private NodeToTypeConverter $nodeTypeConverter
    ) {
    }

    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof QualifiedName);

        $parent = $node->parent;
        if ($parent instanceof CallExpression) {
            return $this->resolveContextFromCall($resolver, $frame, $parent, $node);
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
            $uri = $node->getRoot()->uri;
            if (!$uri) {
                return $context->withType(TypeFactory::string());
            }

            return $context->withType(TypeFactory::stringLiteral(dirname($uri)));
        }

        $type = $this->nodeTypeConverter->resolve($node);

        if ($type instanceof ReflectedClassType) {
            try {
                // fast but inaccurate check to see if class exists
                $this->reflector->sourceCodeForClassLike($type->name());
                // accurate check to see if class exists
                $class = $this->reflector->reflectClassLike($type->name());
                return new ClassLikeContext(
                    $context->symbol(),
                    ByteOffsetRange::fromInts($node->getStartPosition(), $node->getEndPosition()),
                    $class
                );
            } catch (NotFound) {
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
                } catch (NotFound) {
                }
            }
        }


        return $context->withType($type);
    }

    private function resolveContextFromCall(
        NodeContextResolver $resolver,
        Frame $frame,
        CallExpression $parent,
        QualifiedName $node
    ): NodeContext {
        $name = $node->getResolvedName();

        if (null === $name) {
            $name = $node->getNamespacedName();
        }

        $name = Name::fromString((string) $name);
        $range = ByteOffsetRange::fromInts(
            $node->getStartPosition(),
            $node->getEndPosition(),
        );

        try {
            $function = $this->reflector->reflectFunction($name);
        } catch (NotFound $exception) {
            // create dummy function
            $function = VirtualReflectionFunction::empty($name, $range);
        }

        $context = FunctionCallContext::create($name, $range, $function, FunctionArguments::fromList($resolver, $frame, $parent->argumentExpressionList));

        $byReference = $function->parameters()->passedByReference();
        $arguments = null;

        if ($byReference->count()) {
            $arguments = FunctionArguments::fromList(
                $resolver,
                $frame,
                $parent->argumentExpressionList
            );
            foreach ($byReference as $parameter) {
                $argument = $arguments->at($parameter->index());
                $frame->locals()->set(new Variable(
                    name: $argument->symbol()->name(),
                    offset: $argument->symbol()->position()->start()->toInt(),
                    type: $parameter->type(),
                    wasAssigned: false /** $wasAssigned bool */,
                    wasDefined: true /** $wasDefined bool */
                ));
            }
        }

        $stub = $this->registry->get($name->short());
        if ($stub) {
            $arguments = $arguments ?: FunctionArguments::fromList(
                $resolver,
                $frame,
                $parent->argumentExpressionList
            );
            return $stub->resolve($frame, $context, $arguments);
        }

        // the function may have been resolved to a global, so create
        // the context again with the potentially shorter name
        return $context->withSymbolName($function->name()->__toString());
    }
}
