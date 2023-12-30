<?php

namespace Phpactor\Completion\Bridge\TolerantParser\WorseReflection;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\Expression\ObjectCreationExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;

class WorseConstructorCompletor extends AbstractParameterCompletor implements TolerantCompletor
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

        if (!$node instanceof Variable && !$node instanceof ObjectCreationExpression) {
            return true;
        }

        $creationExpression = $node instanceof ObjectCreationExpression ? $node : $node->getFirstAncestor(ObjectCreationExpression::class);

        if (!$creationExpression || ($creationExpression instanceof ObjectCreationExpression && null === $creationExpression->openParen)) {
            return true;
        }

        $variables = $this->variableCompletionHelper->variableCompletions($node, $source, $offset);

        // no variables available for completion, return empty handed
        if ($variables === []) {
            return true;
        }

        assert($creationExpression instanceof ObjectCreationExpression);

        try {
            $reflectionClass = $this->reflectClass($source, $creationExpression);
        } catch (NotFound) {
            return true;
        }

        if (null === $reflectionClass) {
            return true;
        }

        if (false === $reflectionClass->methods()->has('__construct')) {
            return true;
        }

        $reflectionConstruct = $reflectionClass->methods()->get('__construct');

        // function has no parameters, return empty handed
        if ($reflectionConstruct->parameters()->count() === 0) {
            return true;
        }

        $suggestions = $this->populateResponse($creationExpression, $reflectionConstruct, $variables);
        yield from $suggestions;

        return $suggestions->getReturn();
    }

    /**
     * @return ReflectionClass|null
     */
    private function reflectClass(string $source, ObjectCreationExpression $creationExpresion)
    {
        $typeName = $creationExpresion->classTypeDesignator;

        if (!$typeName instanceof QualifiedName) {
            return null;
        }

        $resolvedName = $typeName->getResolvedName();

        if (null === $resolvedName) {
            return null;
        }

        return $this->reflector->reflectClass((string) $resolvedName);
    }
}
