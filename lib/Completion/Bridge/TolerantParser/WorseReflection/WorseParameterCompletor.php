<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use LogicException;
use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionParameter;
use Phpactor\WorseReflection\Core\Type;

class WorseParameterCompletor extends AbstractParameterCompletor implements TolerantCompletor
{
    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        // Tolerant parser _seems_ to resolve f.e. offset 74 as the qualified
        // name of the node, when it is actually the open bracket. If it is a qualified
        // name, we take our chances on the parent.
        if ($node instanceof QualifiedName) {
            $node = $node->parent;
        }

        if ($node instanceof ArgumentExpressionList) {
            $node = $node->parent;
        }

        if (!$node instanceof Variable && !$node instanceof CallExpression) {
            return true;
        }

        $callExpression = $node instanceof CallExpression ? $node : $node->getFirstAncestor(CallExpression::class);

        if (!$callExpression) {
            return true;
        }

        assert($callExpression instanceof CallExpression);
        $callableExpression = $callExpression->callableExpression;

        $variables = $this->variableCompletionHelper->variableCompletions($node, $source, $offset);

        // no variables available for completion, return empty handed
        if (empty($variables)) {
            return true;
        }

        try {
            $reflectionFunctionLike = $this->reflectFunctionLike($source, $callableExpression);
        } catch (NotFound $exception) {
            return true;
        }

        if (null === $reflectionFunctionLike) {
            return true;
        }

        $suggestions = $this->populateResponse($callableExpression, $reflectionFunctionLike, $variables);
        yield from $suggestions;

        return $suggestions->getReturn();
    }

    private function paramIndex(Node $node): int
    {
        $argumentList = $this->argumentListFromNode($node);

        if (null === $argumentList) {
            return 1;
        }

        $index = 0;
        /** @var ArgumentExpression $element */
        foreach ($argumentList->getElements() as $element) {
            $index++;
            if (!$element->expression instanceof Variable) {
                continue;
            }

            $name = $element->expression->getName();

            if ($name instanceof MissingToken) {
                continue;
            }
        }

        // if we have a trailing comma, e.g. the argument list is `$foobar, `
        // then the above elements will contain only `$foobar` but the param
        // index should be incremented.
        if (substr(trim($argumentList->getText()), -1, 1) === ',') {
            return $index + 1;
        }

        return $index;
    }

    private function isVariableValidForParameter(WorseVariable $variable, ReflectionParameter $parameter): bool
    {
        if ($parameter->inferredTypes()->best() == Type::undefined()) {
            return true;
        }

        $valid = false;

        /** @var Type $variableType */
        foreach ($variable->symbolContext()->types() as $variableType) {
            $variableTypeClass = null;
            if ($variableType->isClass()) {
                $variableTypeClass = $this->reflector->reflectClassLike($variableType->className());
            }

            foreach ($parameter->inferredTypes() as $parameterType) {
                if ($variableType == $parameterType) {
                    return true;
                }

                if ($variableTypeClass && $parameterType->isClass() && $variableTypeClass->isInstanceOf($parameterType->className())) {
                    return true;
                }
            }
        }
        return false;
    }

    private function reflectedParameter(ReflectionFunctionLike $reflectionFunctionLike, int $paramIndex): ReflectionParameter
    {
        $reflectedIndex = 1;
        /** @var ReflectionParameter $parameter */
        foreach ($reflectionFunctionLike->parameters() as $parameter) {
            if ($reflectedIndex == $paramIndex) {
                return $parameter;
            }
            $reflectedIndex++;
        }

        throw new LogicException(sprintf('Could not find parameter for index "%s"', $paramIndex));
    }

    private function numberOfArgumentsExceedParameterArity(ReflectionFunctionLike $reflectionFunctionLike, int $paramIndex): bool
    {
        return $reflectionFunctionLike->parameters()->count() < $paramIndex;
    }

    /**
     * @return ReflectionFunctionLike|null
     */
    private function reflectFunctionLike(TextDocument $source, Node $callableExpression)
    {
        $offset = $this->reflector->reflectOffset($source, $callableExpression->getEndPosition());

        if ($containerType = $offset->symbolContext()->containerType()) {
            try {
                $containerClass = $this->reflector->reflectClassLike($containerType->className());
            } catch (NotFound $notFound) {
                return null;
            }
            return $containerClass->methods()->get($offset->symbolContext()->symbol()->name());
        }

        if (!$callableExpression instanceof QualifiedName) {
            return null;
        }

        $name = $callableExpression->getResolvedName() ?? $callableExpression->getText();

        return $this->reflector->reflectFunction((string) $name);
    }

    /**
     * @return ArgumentExpressionList|null
     */
    private function argumentListFromNode(Node $node)
    {
        if ($node instanceof QualifiedName) {
            $callExpression = $node->parent;
            assert($callExpression instanceof CallExpression);
            return $callExpression->argumentExpressionList;
        }
        
        assert($node instanceof MemberAccessExpression || $node instanceof ScopedPropertyAccessExpression);
        assert(null !== $node->parent);

        $list = $node->parent->getFirstDescendantNode(ArgumentExpressionList::class);
        assert($list instanceof ArgumentExpressionList || is_null($list));

        return $list;
    }
}
