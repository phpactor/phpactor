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
use Microsoft\PhpParser\Node\QualifiedName;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Parser;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Indexer\Model\Name\FullyQualifiedName;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\Core\Exception\NotFound;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ClassStringType;
use Phpactor\WorseReflection\Core\Type\ReflectedClassType;
use Phpactor\WorseReflection\Core\Util\NodeUtil;
use Phpactor\WorseReflection\Reflector;

final class SymfonyFormTypeCompletor implements TolerantCompletor
{
    const FORM_BUILDER_INTERFACE = 'Symfony\\Component\\Form\\FormBuilderInterface';
    const FORM_TYPE_INTERFACE = 'Symfony\\Component\\Form\\FormTypeInterface';

    private Parser $parser;

    public function __construct(
        private Reflector $reflector,
        private QueryClient $queryClient,
    ) {
        $this->parser = new Parser();
    }

    public function complete(Node $node, TextDocument $source, ByteOffset $offset): Generator
    {
        $completeFormTypes = false;

        if ($node instanceof ArgumentExpressionList) {
            $argumentExpressionCount = iterator_count($node->getChildNodes());
            if ($argumentExpressionCount === 1) {
                $completeFormTypes = true;
            }
        }

        if ($node instanceof QualifiedName) {
            $argumentExpressionList = $node->parent?->parent;

            if ($argumentExpressionList instanceof ArgumentExpressionList) {
                $arguments = iterator_to_array($argumentExpressionList->getChildNodes());
                if (count($arguments) >= 2) {
                    if ($node->parent === $arguments[1]) {
                        $completeFormTypes = true;
                    }
                }
            }
        }

        $callNode = $node->getFirstAncestor(CallExpression::class);
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

        if ($completeFormTypes) {
            yield from $this->completeFormTypes();
            return;
        }

        $generator = $argumentListNode->getChildNodes();
        $generator->next();

        $formTypeNode = $generator->current();

        if (!($formTypeNode instanceof ArgumentExpression)) {
            return;
        }

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

        foreach ($options as $option => $priority) {
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
                    'priority' => Suggestion::PRIORITY_HIGH + $priority,
                ]
            );
        }
        
        return true;
    }

    private function findFormTypeOptions(?string $fqn, array &$options = [], array &$visited = [], int &$priority = 0): void
    {
        if ($fqn === null) {
            return;
        }

        if (in_array($fqn, $visited)) {
            return;
        }

        try {
            $reflectionClass = $this->reflector->reflectClass($fqn);
        } catch (NotFound) {
            return;
        }

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

        $priority++;

        $membersNode = $ast->getFirstDescendantNode(ClassMembersNode::class);
        $parentFQN = null;

        foreach ($membersNode->getChildNodes() as $member) {
            if ($member instanceof MethodDeclaration) {
                if ($member->getName() === 'getParent') {
                    $parentFQN = $this->extractParentTypeFromMethod($classSourceCode, $member);
                }

                if ($member->getName() === 'configureOptions') {
                    $this->extractOptionsFromConfiguration($member, $options, $priority);
                }

            }
        }

        $this->findFormTypeOptions($extendsFQN, $options, $visited, $priority);
        $this->findFormTypeOptions($parentFQN, $options, $visited, $priority);
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

    private function extractOptionsFromConfiguration(MethodDeclaration $methodDeclaration, array &$options, int $priority): void
    {
        $methodBody = $methodDeclaration->getFirstDescendantNode(CompoundStatementNode::class);
        if (!($methodBody instanceof CompoundStatementNode)) {
            return;
        }

        foreach ($methodBody->statements as $statement) {
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

                if ($methodName == 'setDefault') {
                    $argumentExpression = $callExpression->getFirstDescendantNode(ArgumentExpression::class);
                    if (!($argumentExpression instanceof ArgumentExpression)) {
                        continue;
                    }

                    $expression = $argumentExpression->expression;
                    if (!($expression instanceof StringLiteral)) {
                        continue;
                    }

                    $options[$expression->getText()] = $priority;
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

                            $options[$key->getText()] = $priority;
                        }
                    }
                }
            }
        }
    }

    private function getImplementors(FullyQualifiedName $type, bool $yieldFirst): Generator
    {
        if ($yieldFirst) {
            yield $type;
        }

        foreach ($this->queryClient->class()->implementing($type) as $implementingType) {
            yield from $this->getImplementors($implementingType, true);
        }
    }

    private function completeFormTypes(): Generator
    {
        $fqn = FullyQualifiedName::fromString(self::FORM_TYPE_INTERFACE);

        $implementors = $this->getImplementors($fqn, false);

        foreach ($implementors as $implementor) {
            $record = $this->queryClient->class()->get($implementor);

            if (!$record instanceof ClassRecord) {
                continue;
            }

            $fullyQualifiedName = $record->fqn();
            $className = $record->shortName();

            $reflectionClass = $this->reflector->reflectClassLike($fullyQualifiedName->__toString());
            if (!$reflectionClass->isConcrete()) {
                continue;
            }

            yield Suggestion::createWithOptions(
                $className.'::class',
                [
                    'label' => $className.'::class',
                    'short_description' => '',
                    'documentation' => '',
                    'type' => Suggestion::TYPE_CLASS,
                    'priority' => Suggestion::PRIORITY_HIGH,
                    'class_import' => $fullyQualifiedName,
                ]
            );
        }
    }

}
