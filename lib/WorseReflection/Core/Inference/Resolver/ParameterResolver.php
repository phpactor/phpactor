<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Expression\AnonymousFunctionCreationExpression;
use Microsoft\PhpParser\Node\Expression\ArrowFunctionCreationExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Parameter;
use Microsoft\PhpParser\Node\Statement\FunctionDeclaration;
use Phpactor\WorseReflection\Core\Exception\CouldNotResolveNode;
use Phpactor\WorseReflection\Core\Exception\ItemNotFound;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Phpactor\WorseReflection\Core\Inference\FrameStack;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

class ParameterResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, FrameStack $frameStack, Node $node): NodeContext
    {
        assert($node instanceof Parameter);

        $method = $node->getFirstAncestor(
            ArrowFunctionCreationExpression::class,
            AnonymousFunctionCreationExpression::class,
            MethodDeclaration::class,
            FunctionDeclaration::class
        );

        if ($method instanceof MethodDeclaration) {
            return $this->resolveParameterFromMethodReflection($resolver->reflector(), $method, $node);
        }

        if ($method instanceof FunctionDeclaration) {
            return $this->resolveParameterFromFunctionReflection($resolver->reflector(), $method, $node);
        }

        $typeDeclaration = $node->typeDeclarationList;

        $type = NodeUtil::typeFromQualfiedNameLike($resolver->reflector(), $node, $node->typeDeclarationList);

        if ($node->dotDotDotToken) {
            $type = TypeFactory::array($type);
        }

        if ($node->questionToken) {
            $type = TypeFactory::nullable($type);
        }

        return NodeContextFactory::create(
            (string)$node->variableName->getText($node->getFileContents()),
            $node->variableName->getStartPosition(),
            $node->variableName->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $type,
            ]
        );
    }

    private function resolveParameterFromFunctionReflection(Reflector $reflector, FunctionDeclaration $function, Parameter $node): NodeContext
    {
        $name = $function->getNamespacedName();

        try {
            $function = $reflector->reflectFunction($name->getFullyQualifiedNameText());
        } catch (NotFound $notFound) {
            throw new CouldNotResolveNode(sprintf(
                'Function "%s" not found',
                $name->getFullyQualifiedNameText()
            ), 0, $notFound);
        }

        try {
            $parameter = $function->parameters()->get((string)$node->getName());
        } catch (NotFound $notFound) {
            throw new CouldNotResolveNode(sprintf(
                'Parameter "%s" not found',
                (string)$node->getName(),
            ), 0, $notFound);
        }

        return NodeContextFactory::create(
            (string)$node->variableName->getText($node->getFileContents()),
            $node->variableName->getStartPosition(),
            $node->variableName->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $parameter->inferredType(),
            ]
        );
    }

    private function resolveParameterFromMethodReflection(Reflector $reflector, MethodDeclaration $method, Parameter $node): NodeContext
    {
        $class = NodeUtil::nodeContainerClassLikeDeclaration($node);

        if (null === $class) {
            throw new CouldNotResolveNode(sprintf(
                'Cannot find class context "%s" for parameter',
                $node->getName()
            ));
        }

        try {
            $reflectionClass = $reflector->reflectClassLike($class->getNamespacedName()->__toString());
        } catch (NotFound $notFound) {
            throw new CouldNotResolveNode(sprintf(
                'Class "%s" not found',
                $class->getNamespacedName()->__toString()
            ), 0, $notFound);
        }

        try {
            $reflectionMethod = $reflectionClass->methods()->get($method->getName());
        } catch (ItemNotFound $notFound) {
            throw new CouldNotResolveNode(sprintf(
                'Could not find method "%s" in class "%s"',
                $method->getName(),
                $reflectionClass->name()->__toString()
            ), 0, $notFound);
        }

        if (null === $node->getName()) {
            throw new CouldNotResolveNode(
                'Node name for parameter resolved to NULL'
            );
        }

        if (!$reflectionMethod->parameters()->has($node->getName())) {
            throw new CouldNotResolveNode(sprintf(
                'Cannot find parameter "%s" for method "%s" in class "%s"',
                $node->getName(),
                $reflectionMethod->name(),
                $reflectionClass->name()
            ));
        }

        $reflectionParameter = $reflectionMethod->parameters()->get($node->getName());

        return NodeContextFactory::create(
            (string)$node->variableName->getText($node->getFileContents()),
            $node->variableName->getStartPosition(),
            $node->variableName->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $reflectionParameter->inferredType(),
                'container_type' => $reflectionClass->type(),
            ]
        );
    }
}
