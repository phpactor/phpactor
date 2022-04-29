<?php

namespace Phpactor\WorseReflection\Core\Inference\Walker;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\DelimitedList\ListExpressionList;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Phpactor\WorseReflection\Core\Inference\Frame;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Expression\ListIntrinsicExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Phpactor\WorseReflection\Core\Inference\FrameResolver;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;
use Phpactor\WorseReflection\Core\Type\ArrayShapeType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\TypeUtil;
use Psr\Log\LoggerInterface;

class AssignmentWalker extends AbstractWalker
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    
    public function nodeFqns(): array
    {
        return [AssignmentExpression::class];
    }

    public function walk(FrameResolver $resolver, Frame $frame, Node $node): Frame
    {
        assert($node instanceof AssignmentExpression);

        $rightContext = $resolver->resolveNode($frame, $node->rightOperand);

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
            $this->walkMemberAccessExpression($resolver, $frame, $node->leftOperand, $rightContext);
            return $frame;
        }

        if ($node->leftOperand instanceof SubscriptExpression) {
            $this->walkSubscriptExpression($resolver, $frame, $node->leftOperand, $rightContext);
            return $frame;
        }

        $this->logger->warning(sprintf(
            'Do not know how to assign to left operand "%s"',
            get_class($node->leftOperand)
        ));

        return $frame;
    }

    private function walkParserVariable(Frame $frame, Variable $leftOperand, NodeContext $rightContext): void
    {
        $name = NodeUtil::nameFromTokenOrNode($leftOperand, $leftOperand->name);
        $context = NodeContextFactory::create(
            $name,
            $leftOperand->getStartPosition(),
            $leftOperand->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $rightContext->type(),
            ]
        );

        $frame->locals()->add(WorseVariable::fromSymbolContext($context)->asAssignment());
    }

    private function walkMemberAccessExpression(
        FrameResolver $resolver,
        Frame $frame,
        MemberAccessExpression $leftOperand,
        NodeContext $typeContext
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
        /** @phpstan-ignore-next-line */
        } else {
            $memberType = $resolver->resolveNode($frame, $memberNameNode)->type();

            if (!$memberType instanceof StringType) {
                return;
            }

            $memberName = TypeUtil::valueOrNull($memberType);
        }

        $context = NodeContextFactory::create(
            (string)$memberName,
            $leftOperand->getStartPosition(),
            $leftOperand->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $typeContext->type(),
            ]
        );

        $frame->properties()->add(WorseVariable::fromSymbolContext($context));
    }

    private function walkArrayCreation(Frame $frame, ArrayCreationExpression $leftOperand, NodeContext $symbolContext): void
    {
        $list = $leftOperand->arrayElements;
        if (!$list instanceof ArrayElementList) {
            return;
        }

        $this->walkArrayElements($list->children, $leftOperand, $symbolContext->type(), $frame);
    }

    private function walkList(Frame $frame, ListIntrinsicExpression $leftOperand, NodeContext $symbolContext): void
    {
        $list = $leftOperand->listElements;
        if (!$list instanceof ListExpressionList) {
            return;
        }

        $this->walkArrayElements($list->children, $leftOperand, $symbolContext->type(), $frame);
    }

    private function walkSubscriptExpression(FrameResolver $resolver, Frame $frame, SubscriptExpression $leftOperand, NodeContext $rightContext): void
    {
        if ($leftOperand->postfixExpression instanceof Variable) {
            foreach ($frame->locals()->byName($leftOperand->postfixExpression->getName()) as $variable) {
                $type = $variable->type();
                if (!$type instanceof ArrayLiteral) {
                    return;
                }

                $frame->locals()->add($variable->withType($type->add($rightContext->type()))->withOffset($leftOperand->getStartPosition()));
                ;
            }
        }

        if ($leftOperand->postfixExpression instanceof MemberAccessExpression) {
            $rightContext = $rightContext->withType(TypeFactory::array());
            $this->walkMemberAccessExpression($resolver, $frame, $leftOperand->postfixExpression, $rightContext);
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
     */
    private function walkArrayElements(array $elements, Node $leftOperand, Type $type, Frame $frame): void
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
        
            $varName = NodeUtil::nameFromTokenOrNode($leftOperand, $elementValue->name);

            $variableContext = NodeContextFactory::create(
                (string)$varName,
                $element->getStartPosition(),
                $element->getEndPosition(),
                [
                    'symbol_type' => Symbol::VARIABLE,
                ]
            );

            if ($type instanceof ArrayShapeType) {
                $variableContext = $variableContext->withType($type->typeAtOffset($index));
            }
        
            if ($type instanceof ArrayLiteral) {
                $variableContext = $variableContext->withType($type->typeAtOffset($index));
            }
        
            $frame->locals()->add(WorseVariable::fromSymbolContext($variableContext));
        }
    }
}
