<?php

namespace Phpactor\WorseReflection\Core\Inference\Resolver;

use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\DelimitedList\ListExpressionList;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\Variable;
use Microsoft\PhpParser\Node\Expression\ListIntrinsicExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\Expression\SubscriptExpression;
use Phpactor\WorseReflection\Core\Inference\NodeContext;
use Microsoft\PhpParser\Node\Expression\AssignmentExpression;
use Phpactor\WorseReflection\Core\Inference\NodeContextResolver;
use Phpactor\WorseReflection\Core\Inference\Resolver;
use Phpactor\WorseReflection\Core\Inference\Symbol;
use Phpactor\WorseReflection\Core\Inference\NodeContextFactory;
use Phpactor\WorseReflection\Core\Inference\Variable as WorseVariable;
use Microsoft\PhpParser\Token;
use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\AggregateType;
use Phpactor\WorseReflection\Core\Type\ArrayAccessType;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\Literal;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\TypeUtil;

class AssignmentExpressionResolver implements Resolver
{
    public function resolve(NodeContextResolver $resolver, NodeContext $context, Node $node): NodeContext
    {
        assert($node instanceof AssignmentExpression);

        $rightContext = $resolver->resolveNode($context, $node->rightOperand);

        if ($this->hasMissingTokens($node)) {
            return $context;
        }

        if ($node->leftOperand instanceof Variable) {
            $this->walkParserVariable($context, $node->leftOperand, $rightContext);
            return $context;
        }

        if ($node->leftOperand instanceof ListIntrinsicExpression) {
            $this->walkList($context, $node->leftOperand, $rightContext);
            return $context;
        }

        if ($node->leftOperand instanceof ArrayCreationExpression) {
            $this->walkArrayCreation($context, $node->leftOperand, $rightContext);
            return $context;
        }

        if ($node->leftOperand instanceof MemberAccessExpression) {
            $this->walkMemberAccessExpression($resolver, $context, $node->leftOperand, $rightContext);
            return $context;
        }

        if ($node->leftOperand instanceof SubscriptExpression) {
            $this->walkSubscriptExpression($resolver, $context, $node->leftOperand, $rightContext);
            return $context;
        }

        return $context;
    }

    private function walkParserVariable(NodeContext $context, Variable $leftOperand, NodeContext $rightContext): void
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

        $context->frame()->locals()->set(WorseVariable::fromSymbolContext($context)->asAssignment());
    }

    private function walkMemberAccessExpression(
        NodeContextResolver $resolver,
        NodeContext $context,
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
            $memberType = $resolver->resolveNode($context, $memberNameNode)->type();

            if (!$memberType instanceof StringType) {
                return;
            }

            $memberName = TypeUtil::valueOrNull($memberType);
        }

        $varContext = NodeContextFactory::create(
            (string)$memberName,
            $leftOperand->getStartPosition(),
            $leftOperand->getEndPosition(),
            [
                'symbol_type' => Symbol::VARIABLE,
                'type' => $typeContext->type(),
            ]
        );

        $context->frame()->properties()->set(WorseVariable::fromSymbolContext($varContext));
    }

    private function walkArrayCreation(NodeContext $context, ArrayCreationExpression $leftOperand, NodeContext $nodeContext): void
    {
        $list = $leftOperand->arrayElements;
        if (!$list instanceof ArrayElementList) {
            return;
        }

        $this->walkArrayElements($list->children, $leftOperand, $nodeContext->type(), $context);
    }

    private function walkList(NodeContext $context, ListIntrinsicExpression $leftOperand, NodeContext $nodeContext): void
    {
        $list = $leftOperand->listElements;
        if (!$list instanceof ListExpressionList) {
            return;
        }

        $this->walkArrayElements($list->children, $leftOperand, $nodeContext->type(), $context);
    }

    private function walkSubscriptExpression(NodeContextResolver $resolver, NodeContext $context, SubscriptExpression $leftOperand, NodeContext $rightContext): void
    {
        if ($leftOperand->postfixExpression instanceof Variable) {
            foreach ($context->frame()->locals()->byName((string)$leftOperand->postfixExpression->getName()) as $variable) {
                $type = $variable->type();

                if (!$type instanceof ArrayLiteral) {
                    return;
                }

                // array key specified, e.g. `$foo['bar'] = `
                // @phpstan-ignore-next-line TP lies
                if ($leftOperand->accessExpression) {
                    $accessType = $resolver->resolveNode($context, $leftOperand->accessExpression)->type();

                    if (!$accessType instanceof Literal) {
                        $context->frame()->locals()->set(
                            $variable->withType(
                                new ArrayType(TypeFactory::undefined(), $rightContext->type())
                            )
                        );
                        return;
                    }

                    $context->frame()->locals()->set(
                        $variable->withType(
                            $type->set($accessType->value(), $rightContext->type())
                        )->withOffset($leftOperand->getStartPosition())
                    );
                    continue;
                }

                // @phpstan-ignore-next-line TP lies
                if ($rightContext->type() instanceof Literal) {
                    $context->frame()->locals()->set(
                        $variable->withType(
                            $type->add($rightContext->type())
                        )->withOffset($leftOperand->getStartPosition())
                    );
                    continue;
                }

                $context->frame()->locals()->set(
                    $variable->withType(
                        TypeFactory::array($rightContext->type())
                    )->withOffset($leftOperand->getStartPosition())
                );
            }
        }

        if ($leftOperand->postfixExpression instanceof MemberAccessExpression) {
            $rightContext = $rightContext->withType(TypeFactory::array());
            $this->walkMemberAccessExpression($resolver, $context, $leftOperand->postfixExpression, $rightContext);
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
    private function walkArrayElements(array $elements, Node $leftOperand, Type $type, NodeContext $context): void
    {
        $index = -1;
        foreach ($elements as $element) {
            if (!$element instanceof ArrayElement) {
                continue;
            }

            $index++;
            $elementValue = $element->elementValue;
            if ($elementValue instanceof ArrayCreationExpression) {
                $list = $elementValue->arrayElements;
                if (!$list instanceof ArrayElementList) {
                    return;
                }
                $accessType = $this->offsetType($type, $index);
                $this->walkArrayElements($list->children, $leftOperand, $accessType, $context);
                continue;
            }
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


            $variableContext = $variableContext->withType($this->offsetType($type, $index));
            $context->frame()->locals()->set(WorseVariable::fromSymbolContext($variableContext));
        }
    }

    private function offsetType(Type $type, int $index): Type
    {
        if ($type instanceof ArrayAccessType) {
            return $type->typeAtOffset($index);
        }

        if ($type instanceof AggregateType) {
            $agg = [];
            foreach ($type->types as $type) {
                $agg[] = $this->offsetType($type, $index);
            }
            return $type->fromTypes(...$agg);
        }

        return new MissingType();
    }
}
