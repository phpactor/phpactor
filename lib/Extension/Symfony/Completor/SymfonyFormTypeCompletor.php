<?php

namespace Phpactor\Extension\Symfony\Completor;

use Generator;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\ArrayElement;
use Microsoft\PhpParser\Node\ClassBaseClause;
use Microsoft\PhpParser\Node\ClassMembersNode;
use Microsoft\PhpParser\Node\DelimitedList\ArgumentExpressionList;
use Microsoft\PhpParser\Node\DelimitedList\ArrayElementList;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\ArrayCreationExpression;
use Microsoft\PhpParser\Node\Expression\CallExpression;
use Microsoft\PhpParser\Node\Expression\MemberAccessExpression;
use Microsoft\PhpParser\Node\MethodDeclaration;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Parser;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Extension\LanguageServer\Logger\ClientLogger;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassStringType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

final class SymfonyFormTypeCompletor implements TolerantCompletor
{
    const FORM_BUILDER_INTERFACE = 'Symfony\\Component\\Form\\FormBuilderInterface';

    private Parser $parser;

    public function __construct(private Reflector $reflector, private ClientLogger $clientLogger)
    {
        $this->parser = new Parser();
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $inQuote = false;
        if ($node instanceof StringLiteral) {
            $inQuote = true;

            if (!($node->parent instanceof ArrayElement)) {
                return;
            }

            $arrayElementNode = $node->parent;

            $arrayChildNodes = $arrayElementNode->getChildNodes();
            $arrayChildNodes->next();

            $isLHS = $arrayChildNodes->current() !== $node;

            if (!$isLHS) {
                return;
            }

            if (!($arrayElementNode->parent instanceof ArrayElementList)) {
                return;
            }
        } else {
            if (!($node instanceof ArrayCreationExpression)) {
                return;
            }

            $arrayElementNode = $node;
        }

        $callNode = $arrayElementNode->getFirstAncestor(CallExpression::class);
        if (!($callNode instanceof CallExpression)) {
            return;
        }

        $argumentListNode = $callNode->getFirstDescendantNode(ArgumentExpressionList::class);

        if (!($argumentListNode instanceof ArgumentExpressionList)) {
            return;
        }

        $memberAccess = $callNode->callableExpression;

        if (!$memberAccess instanceof MemberAccessExpression) {
            return;
        }

        $methodName = NodeUtil::nameFromTokenOrNode($callNode, $memberAccess->memberName);

        if ($methodName !== 'add') {
            return;
        }

        $expression = $memberAccess->dereferencableExpression;
        $containerType = $this->reflector->reflectOffset($source, $expression->getEndPosition())->nodeContext()->type();

        if ($containerType->instanceof(TypeFactory::class(self::FORM_BUILDER_INTERFACE))->isFalseOrMaybe()) {
            return;
        }

        $generator = $argumentListNode->getChildNodes();
        $generator->next();

        $formTypeNode = $generator->current();

        if (!($formTypeNode instanceof ArgumentExpression)) {
            return;
        }

        $formTypeClassType = $this->reflector->reflectOffset($source, $formTypeNode->getEndPosition())->nodeContext()->type();

        if (!($formTypeClassType instanceof ClassStringType)) {
            return;
        }

        $formTypeClassFQN = $formTypeClassType->className()?->full();

        if ($formTypeClassFQN === null) {
            return;
        }

        $options = [];

        $this->findFormTypeOptions($formTypeClassFQN, $options);

        foreach ($options as $option => $v) {

            if ($inQuote) {
                $option = trim($option, '\'');
            }

            yield Suggestion::createWithOptions(
                $option,
                [
                    'label' => $option,
                    'short_description' => '',
                    'documentation' => '',
                    'type' => Suggestion::TYPE_CONSTANT,
                    'priority' => 555,
                ]
            );
        }

        return true;
    }

    private function findFormTypeOptions(?string $fqn, array &$options = [], array &$visited = []): void
    {
        if ($fqn === null) {
            return;
        }

        if (in_array($fqn, $visited)) {
            return;
        }

        $reflectionClass = $this->reflector->reflectClass($fqn);
        $classSourceCode = $reflectionClass->sourceCode();

        $visited[] = $fqn;

        $ast = $this->parser->parseSourceFile($classSourceCode);
        $extendsTag = $ast->getFirstDescendantNode(ClassBaseClause::class);

        $extendsFQN = null;

        if ($extendsTag) {
            if (!($extendsTag instanceof ClassBaseClause)) {
                return;
            }

            $extendsClassType = $this->reflector->reflectOffset($classSourceCode, $extendsTag->baseClass->getEndPosition())->nodeContext()->type();

            if (!($extendsClassType instanceof ReflectedClassType)) {
                return;
            }

            $extendsFQN = $extendsClassType->name()?->full();
        }

        $membersNode = $ast->getFirstDescendantNode(ClassMembersNode::class);
        $parentFQN = null;

        foreach ($membersNode->getChildNodes() as $member) {
            if ($member instanceof MethodDeclaration) {
                if ($member->getName() === 'getParent') {
                    $parentFQN = $this->extractParentTypeFromMethod($classSourceCode, $member);
                }

                if ($member->getName() === 'configureOptions') {
                    $this->extractOptionsFromConfiguration($member, $options);
                }

            }
        }

        $this->findFormTypeOptions($extendsFQN, $options, $visited);
        $this->findFormTypeOptions($parentFQN, $options, $visited);
    }

    private function extractParentTypeFromMethod(TextDocument $source, MethodDeclaration $methodDeclaration): ?string
    {
        $methodBody = $methodDeclaration->getFirstDescendantNode(CompoundStatementNode::class);
        if (!($methodBody instanceof CompoundStatementNode)) {
            return null;
        }

        foreach ($methodBody->statements as $statement) {
            if ($statement instanceof ReturnStatement) {
                $returnExpression = $statement->expression;
                $reflectOffset = $this->reflector->reflectOffset($source, $returnExpression->getEndPosition())->nodeContext()->type();

                if (!($reflectOffset instanceof ClassStringType)) {
                    return null;
                }

                return $reflectOffset->className()?->full();
            }
        }

        return null;
    }

    private function extractOptionsFromConfiguration(MethodDeclaration $methodDeclaration, array &$options): void
    {
        $methodBody = $methodDeclaration->getFirstDescendantNode(CompoundStatementNode::class);
        if (!($methodBody instanceof CompoundStatementNode)) {
            return;
        }

        foreach ($methodBody->statements as $statement) {
            // we're looking for setDefaults([x => y])
            // and setDefault(x, y)
            // and we're collecting x
            if ($statement instanceof ExpressionStatement) {
                $callExpression = $statement->getFirstDescendantNode(CallExpression::class);
                if (!($callExpression instanceof CallExpression)) {
                    continue;
                }

                $callableExpression = $callExpression->callableExpression;
                if (!$callableExpression instanceof MemberAccessExpression) {
                    continue;
                }

                $methodName = NodeUtil::nameFromTokenOrNode($callExpression, $callableExpression->memberName);

                if ($methodName === 'setInfo') {
                    continue;
                }
                if ($methodName === 'setAllowedTypes') {
                    continue;
                }

                if ($methodName == 'setDefault') {

                }

                if ($methodName == 'setDefaults') {
                    $arrayElementList = $callExpression->getFirstDescendantNode(ArrayElementList::class);
                    if (!($arrayElementList instanceof ArrayElementList)) {
                        continue;
                    }

                    $arrayElements = $arrayElementList->children;
                    foreach ($arrayElements as $arrayElement) {
                        if ($arrayElement instanceof ArrayElement) {
                            $key = $arrayElement->elementKey;
                            if (!$key instanceof StringLiteral) {
                                continue;
                            }

                            $options[$key->getText()] = 1;
                        }
                    }
                }
            }
        }
    }
}
