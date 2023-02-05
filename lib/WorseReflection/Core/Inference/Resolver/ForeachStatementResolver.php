<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\ForeachKey;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Frame;
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
    public function resolve(NodeContextResolver $resolver, Frame $frame, Node $node): NodeContext
    {
        assert($node instanceof ForeachStatement);
        $context = NodeContextFactory::forNode($node);
        $nodeContext = $resolver->resolveNode($frame, $node->forEachCollectionName);
        $this->processKey($resolver, $node, $frame, $nodeContext->type());
        $this->processValue($resolver, $node, $frame, $nodeContext);

        $this->addAssignedVarsInCompoundStatement($node, $resolver, $frame);

        return $context;
    }

    private function processValue(NodeContextResolver $resolver, ForeachStatement $node, Frame $frame, NodeContext $nodeContext): void
    {
        $itemName = $node->foreachValue;

        if (!$itemName instanceof ForeachValue) {
            return;
        }

        $expression = $itemName->expression;
        if ($expression instanceof Variable) {
            $this->valueFromVariable($expression, $node, $nodeContext, $frame);
            return;
        }

        if ($expression instanceof ArrayCreationExpression) {
            $this->valueFromArrayCreation($resolver, $expression, $node, $nodeContext, $frame);
        }
    }

    private function processKey(NodeContextResolver $resolver, ForeachStatement $node, Frame $frame, Type $type): void
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

        $context = NodeContextFactory::create(
            $itemName,
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
            ]
        );
        if ($type instanceof IterableType) {
            $context = $context->withType($this->resolveKeyType($type));
        }

        $frame->locals()->set(WorseVariable::fromSymbolContext($context));
    }

    private function valueFromVariable(Variable $expression, ForeachStatement $node, NodeContext $nodeContext, Frame $frame): void
    {
        $itemName = $expression->getText();

        if (!is_string($itemName)) {
            return;
        }

        $type = $nodeContext->type();

        $context = NodeContextFactory::create(
            $itemName,
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
            ]
        );

        if ($type instanceof ReflectedClassType) {
            $context = $context->withType($type->iterableValueType());
        }
        if ($type instanceof IterableType) {
            $context = $context->withType($this->resolveValueType($type));
        }

        $frame->locals()->set(WorseVariable::fromSymbolContext($context));
    }

    private function valueFromArrayCreation(
        NodeContextResolver $resolver,
        ArrayCreationExpression $expression,
        ForeachStatement $node,
        NodeContext $nodeContext,
        Frame $frame
    ): void {
        $elements = $expression->arrayElements;
        if (!$elements instanceof ArrayElementList) {
            return;
        }

        $arrayType = $nodeContext->type();

        if (!$arrayType instanceof IterableType) {
            return;
        }

        $index = 0;

        foreach ($elements->children as $item) {
            if (!$item instanceof ArrayElement) {
                continue;
            }

            $context = $resolver->resolveNode($frame, $item->elementValue);
            $context = $context->withType($this->resolveArrayCreationType($arrayType, $index));

            $frame->locals()->set(WorseVariable::fromSymbolContext($context));
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

    private function addAssignedVarsInCompoundStatement(ForeachStatement $node, NodeContextResolver $resolver, Frame $frame): void
    {
        $compoundStatement = $node->statements;
        if ($compoundStatement instanceof CompoundStatementNode) {
            foreach ($compoundStatement->statements as $statement) {
                $resolver->resolveNode($frame, $statement);
            }
            foreach ($frame->locals()->greaterThan(
                $compoundStatement->openBrace->getStartPosition()
            )->lessThan(
                $compoundStatement->closeBrace->getStartPosition()
            ) as $local) {
                if (!$local->wasAssigned()) {
                    continue;
                }
                if ($previous = $frame->locals()->lessThan($local->offset())->byName($local->name())->lastOrNull()) {
                    $type = $previous->type()->addType($local->type())->reduce();
                    $frame->locals()->set(
                        $previous->withType($type)->withOffset($compoundStatement->closeBrace->getEndPosition())
                    );
                }
            }
        }
    }
}
