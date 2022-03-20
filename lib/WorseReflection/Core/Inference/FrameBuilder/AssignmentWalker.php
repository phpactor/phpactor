<?php

namespace Phpactor\WorseReflection\Core\Inference\FrameBuilder;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\DelimitedList\ListExpressionList;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Expression\ListIntrinsicExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Phpactor\WorseReflection\Core\Inference\SymbolContext;
use Phpactor\WorseReflection\Core\Inference\FrameBuilder;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Phpactor\WorseReflection\Core\TypeFactory;
use Psr\Log\LoggerInterface;

class AssignmentWalker extends AbstractWalker
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function canWalk(Node $node): bool
    {
        return $node instanceof AssignmentExpression;
    }

    public function walk(FrameBuilder $builder, Frame $frame, Node $node): Frame
    {
        assert($node instanceof AssignmentExpression);

        $rightContext = $builder->resolveNode($frame, $node->rightOperand);

        if ($this->hasMissingTokens($node)) {
            return $frame;
        }

        if ($node->leftOperand instanceof Variable) {
            $this->walkParserVariable($frame, $node->leftOperand, $rightContext);
            return $frame;
        }

        if ($node->leftOperand instanceof ListIntrinsicExpression) {
            $this->walkList($frame, $node->leftOperand, $rightContext);
            return $frame;
        }

        if ($node->leftOperand instanceof ArrayCreationExpression) {
            $this->walkArrayCreation($frame, $node->leftOperand, $rightContext);
            return $frame;
        }

        if ($node->leftOperand instanceof MemberAccessExpression) {
            $this->walkMemberAccessExpression($builder, $frame, $node->leftOperand, $rightContext);
            return $frame;
        }

        if ($node->leftOperand instanceof SubscriptExpression) {
            $this->walkSubscriptExpression($builder, $frame, $node->leftOperand, $rightContext);
            return $frame;
        }

        $this->logger->warning(sprintf(
            'Do not know how to assign to left operand "%s"',
            get_class($node->leftOperand)
        ));

        return $frame;
    }

    private function walkParserVariable(Frame $frame, Variable $leftOperand, SymbolContext $rightContext): void
    {
        /** @phpstan-ignore-next-line */
        $name = $leftOperand->name->getText($leftOperand->getFileContents());
        $context = $this->symbolFactory()->context(
            $name,
            $leftOperand->getStartPosition(),
            $leftOperand->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $rightContext->type(),
                'value' => $rightContext->value(),
            ]
        );

        $frame->locals()->add(WorseVariable::fromSymbolContext($context));
    }

    private function walkMemberAccessExpression(
        FrameBuilder $builder,
        Frame $frame,
        MemberAccessExpression $leftOperand,
        SymbolContext $typeContext
    ): void {
        $variable = $leftOperand->dereferencableExpression;

        // we do not track assignments to other classes.
        if (false === in_array($variable, [ '$this', 'self' ])) {
            return;
        }

        $memberNameNode = $leftOperand->memberName;

        // TODO: Sort out this mess.
        //       If the node is not a token (e.g. it is a variable) then
        //       evaluate the variable (e.g. $this->$foobar);
        if ($memberNameNode instanceof Token) {
            $memberName = $memberNameNode->getText($leftOperand->getFileContents());
        } else {
            $memberNameInfo = $builder->resolveNode($frame, $memberNameNode);

            if (false === is_string($memberNameInfo->value())) {
                return;
            }

            $memberName = $memberNameInfo->value();
        }

        $context = $this->symbolFactory()->context(
            $memberName,
            $leftOperand->getStartPosition(),
            $leftOperand->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $typeContext->type(),
                'value' => $typeContext->value(),
            ]
        );

        $frame->properties()->add(WorseVariable::fromSymbolContext($context));
    }

    private function walkArrayCreation(Frame $frame, ArrayCreationExpression $leftOperand, SymbolContext $symbolContext): void
    {
        $list = $leftOperand->arrayElements;
        $value = $symbolContext->value();
        if (!$list instanceof ArrayElementList) {
            return;
        }

        $this->walkArrayElements($list->children, $leftOperand, $value, $frame);
    }

    private function walkList(Frame $frame, ListIntrinsicExpression $leftOperand, SymbolContext $symbolContext): void
    {
        $list = $leftOperand->listElements;
        $value = $symbolContext->value();
        if (!$list instanceof ListExpressionList) {
            return;
        }

        $this->walkArrayElements($list->children, $leftOperand, $value, $frame);
    }

    private function walkSubscriptExpression(FrameBuilder $builder, Frame $frame, SubscriptExpression $leftOperand, SymbolContext $rightContext): void
    {
        if ($leftOperand->postfixExpression instanceof MemberAccessExpression) {
            $rightContext = $rightContext->withType(TypeFactory::array());
            $this->walkMemberAccessExpression($builder, $frame, $leftOperand->postfixExpression, $rightContext);
        }
    }

    private function hasMissingTokens(AssignmentExpression $node): bool
    {
        // this would probably never happen ...
        if (false === $node->parent instanceof ExpressionStatement) {
            return false;
        }

        foreach ($node->parent->getDescendantTokens() as $token) {
            if ($token instanceof MissingToken) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * @param mixed[] $elements
     * @param mixed $value
     */
    private function walkArrayElements(array $elements, Node $leftOperand, $value, Frame $frame): void
    {
        $index = -1;
        foreach ($elements as $element) {
            if (!$element instanceof ArrayElement) {
                continue;
            }
        
            $index++;
            $elementValue = $element->elementValue;
        
            if (!$elementValue instanceof Variable) {
                continue;
            }
        
            /** @phpstan-ignore-next-line */
            if (null === $elementValue || null === $elementValue->name) {
                continue;
            }
        
            $varName = $elementValue->name->getText($leftOperand->getFileContents());
            $variableContext = $this->symbolFactory()->context(
                $varName,
                $element->getStartPosition(),
                $element->getEndPosition(),
                [
                    'symbol_type' => Symbol::VARIABLE,
                ]
            );
        
            if (is_array($value) && isset($value[$index])) {
                $variableContext = $variableContext->withValue($value[$index]);
                $variableContext = $variableContext->withType(TypeFactory::fromString(gettype($value[$index])));
            }
        
            $frame->locals()->add(WorseVariable::fromSymbolContext($variableContext));
        }
    }
}
