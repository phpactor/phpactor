<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\ForeachKey;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Microsoft\PhpParser\Node\Statement\ForeachStatement;
use Microsoft\PhpParser\Node\ForeachValue;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\IterableType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Type\UnionType;

class ForeachStatementResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof ForeachStatement);
        $collectionContext = $resolver->resolveNode($context, $node->forEachCollectionName);
        $this->processKey($resolver, $node, $context, $collectionContext->type());
        $this->processValue($resolver, $node, $context, $collectionContext);

        $this->addAssignedVarsInCompoundStatement($node, $resolver, $context);

        return $context;
    }

    private function processValue(NodeContextResolver $resolver, ForeachStatement $node, NodeContext $context, NodeContext $collectionContext): void
    {
        $itemName = $node->foreachValue;

        if (!$itemName instanceof ForeachValue) {
            return;
        }

        $expression = $itemName->expression;
        if ($expression instanceof Variable) {
            $this->valueFromVariable($expression, $node, $collectionContext, $context);
            return;
        }

        if ($expression instanceof ArrayCreationExpression) {
            $this->valueFromArrayCreation($resolver, $expression, $node, $collectionContext, $context);
        }
    }

    private function processKey(NodeContextResolver $resolver, ForeachStatement $node, NodeContext $context, Type $type): void
    {
        $itemName = $node->foreachKey;

        if (!$itemName instanceof ForeachKey) {
            return;
        }

        $expression = $itemName->expression;
        if (!$expression instanceof Variable) {
            return;
        }

        $itemName = $expression->name->getText($node->getFileContents());

        if (!is_string($itemName)) {
            return;
        }

        $varContext = NodeContextFactory::create(
            $itemName,
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
            ]
        );
        if ($type instanceof IterableType) {
            $varContext = $varContext->withType($this->resolveKeyType($type));
        }

        $context->frame()->locals()->set(WorseVariable::fromSymbolContext($varContext));
    }

    private function valueFromVariable(Variable $expression, ForeachStatement $node, NodeContext $collectionContext, NodeContext $context): void
    {
        $itemName = $expression->getText();

        if (!is_string($itemName)) {
            return;
        }

        $type = $collectionContext->type();

        $valueContext = NodeContextFactory::create(
            $itemName,
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
            ]
        );

        if ($type instanceof ReflectedClassType) {
            $valueContext = $valueContext->withType($type->iterableValueType());
        }
        if ($type instanceof IterableType) {
            $valueContext = $valueContext->withType($this->resolveValueType($type));
        }

        $context->frame()->locals()->set(WorseVariable::fromSymbolContext($valueContext));
    }

    private function valueFromArrayCreation(
        NodeContextResolver $resolver,
        ArrayCreationExpression $expression,
        ForeachStatement $node,
        NodeContext $collectionContext,
        NodeContext $context
    ): void {
        $elements = $expression->arrayElements;
        if (!$elements instanceof ArrayElementList) {
            return;
        }

        $arrayType = $collectionContext->type();

        if (!$arrayType instanceof IterableType) {
            return;
        }

        $index = 0;

        foreach ($elements->children as $item) {
            if (!$item instanceof ArrayElement) {
                continue;
            }

            $elContext = $resolver->resolveNode($context, $item->elementValue);
            $elContext = $elContext->withType($this->resolveArrayCreationType($arrayType, $index));

            $context->frame()->locals()->set(WorseVariable::fromSymbolContext($elContext));
            $index++;
        }
    }

    private function resolveArrayCreationType(IterableType $arrayType, int $index): Type
    {
        if ($arrayType instanceof ArrayLiteral) {
            $possibleTypes = [];
            foreach ($arrayType->iterableValueTypes() as $type) {
                if ($type instanceof ArrayLiteral) {
                    $possibleTypes[] = $type->typeAtOffset($index);
                }
            }

            return (new UnionType(...$possibleTypes))->reduce();
        }

        if ($arrayType instanceof ArrayType) {
            $value = $arrayType->iterableValueType();
            if ($value instanceof IterableType) {
                return $value->iterableValueType();
            }
        }

        return new MixedType();
    }

    private function resolveValueType(IterableType $type): Type
    {
        if ($type instanceof ArrayLiteral) {
            return (new UnionType(...$type->iterableValueTypes()));
        }

        return $type->iterableValueType();
    }

    private function resolveKeyType(IterableType $type): Type
    {
        if ($type instanceof ArrayLiteral) {
            return (new UnionType(...$type->iterableKeyTypes()));
        }

        return $type->iterableKeyType();
    }

    private function addAssignedVarsInCompoundStatement(ForeachStatement $node, NodeContextResolver $resolver, NodeContext $context): void
    {
        $compoundStatement = $node->statements;
        if ($compoundStatement instanceof CompoundStatementNode) {
            foreach ($compoundStatement->statements as $statement) {
                $resolver->resolveNode($context, $statement);
            }
            foreach ($context->frame()->locals()->greaterThan(
                $compoundStatement->openBrace->getStartPosition()
            )->lessThan(
                $compoundStatement->closeBrace->getStartPosition()
            ) as $local) {
                if (!$local->wasAssigned()) {
                    continue;
                }
                if ($previous = $context->frame()->locals()->lessThan($local->offset())->byName($local->name())->lastOrNull()) {
                    $type = $previous->type()->addType($local->type())->reduce();
                    $context->frame()->locals()->set(
                        $previous->withType($type)->withOffset($compoundStatement->closeBrace->getEndPosition())
                    );
                }
            }
        }
    }
}
