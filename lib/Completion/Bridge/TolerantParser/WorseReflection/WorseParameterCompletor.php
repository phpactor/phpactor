<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionFunctionLike;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;

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
        } catch (NotFound) {
            return true;
        }

        if (null === $reflectionFunctionLike) {
            return true;
        }

        $suggestions = $this->populateResponse($callableExpression, $reflectionFunctionLike, $variables);
        yield from $suggestions;

        return $suggestions->getReturn();
    }

    /**
     * @return ReflectionFunctionLike|null
     */
    private function reflectFunctionLike(TextDocument $source, Node $callableExpression)
    {
        $offset = $this->reflector->reflectOffset($source, $callableExpression->getEndPosition());

        $containerType = $offset->symbolContext()->containerType();
        if ($containerType->isDefined()) {
            $containerType = $containerType->expandTypes()->classLike()->firstOrNull();
            if (!$containerType instanceof ReflectedClassType) {
                return null;
            }

            $containerClass = $containerType->reflectionOrNull();

            if ($containerClass === null) {
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
}
