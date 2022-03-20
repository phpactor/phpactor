<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\ForeachKey;
use Microsoft\PhpParser\Node;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node\Statement\ForeachStatement;
use Microsoft\PhpParser\Node\ForeachValue;
use Microsoft\PhpParser\Node\Expression\Variable;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\Core\Type\IterableType;

class ForeachWalker extends AbstractWalker
{
    public function canWalk(Node $node): bool
    {
        return $node instanceof ForeachStatement;
    }

    public function walk(FrameBuilder $builder, Frame $frame, Node $node): Frame
    {
        assert($node instanceof ForeachStatement);
        $collection = $builder->resolveNode($frame, $node->forEachCollectionName);
        $this->processKey($node, $frame, $collection);
        $this->processValue($builder, $node, $frame, $collection);

        return $frame;
    }

    private function processValue(FrameBuilder $builder, ForeachStatement $node, Frame $frame, SymbolContext $collection): void
    {
        $itemName = $node->foreachValue;
        
        if (!$itemName instanceof ForeachValue) {
            return;
        }
        
        $expression = $itemName->expression;
        if ($expression instanceof Variable) {
            $this->valueFromVariable($expression, $node, $collection, $frame);
            return;
        }

        if ($expression instanceof ArrayCreationExpression) {
            $this->valueFromArrayCreation($builder, $expression, $node, $collection, $frame);
        }
    }

    private function processKey(ForeachStatement $node, Frame $frame, SymbolContext $collection): void
    {
        $itemName = $node->foreachKey;
        
        if (!$itemName instanceof ForeachKey) {
            return;
        }
        
        $expression = $itemName->expression;
        if (!$expression instanceof Variable) {
            return;
        }
        
        /** @phpstan-ignore-next-line */
        $itemName = $expression->name->getText($node->getFileContents());

        if (!is_string($itemName)) {
            return;
        }
        
        $collectionType = $collection->types()->best();
        
        $context = $this->symbolFactory()->context(
            $itemName,
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
            ]
        );
        
        $frame->locals()->add(WorseVariable::fromSymbolContext($context));
    }

    private function valueFromVariable(Variable $expression, ForeachStatement $node, SymbolContext $collection, Frame $frame): void
    {
        $itemName = $expression->getText();
        
        if (!is_string($itemName)) {
            return;
        }

        $collectionType = $collection->types()->best();

        $context = $this->symbolFactory()->context(
            $itemName,
            $node->getStartPosition(),
            $node->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
            ]
        );
        
        if ($collectionType instanceof GenericClassType) {
            $context = $context->withType($collectionType->iterableValueType());
        }
        if ($collectionType instanceof ArrayType) {
            $context = $context->withType($collectionType->valueType);
        }
        
        $frame->locals()->add(WorseVariable::fromSymbolContext($context));
    }

    private function valueFromArrayCreation(
        FrameBuilder $builder,
        ArrayCreationExpression $expression,
        ForeachStatement $node,
        SymbolContext $collection,
        Frame $frame
    ): void {
        $elements = $expression->arrayElements;
        if (!$elements instanceof ArrayElementList) {
            return;
        }

        $collectionType = $collection->type();
        $values = (array)$collection->value();
        $index = 0;
        foreach ($elements->children as $child) {
            if (!$child instanceof ArrayElement) {
                continue;
            }

            $context = $builder->resolveNode($frame, $child->elementValue);
            if ($collectionType instanceof IterableType) {
                $context = $context->withType($collectionType->iterableValueType());
            }

            if (isset($values[$index])) {
                $context = $context->withValue($values[$index]);
            }

            $frame->locals()->add(WorseVariable::fromSymbolContext($context));
            $index++;
        }
    }
}
