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
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Microsoft\PhpParser\Node\Statement\CompoundStatementNode;
use Microsoft\PhpParser\Node\Statement\ExpressionStatement;
use Microsoft\PhpParser\Node\Statement\ReturnStatement;
use Microsoft\PhpParser\Node\StringLiteral;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Indexer\Model\Name\FullyQualifiedName;
use Phpactor\Indexer\Model\QueryClient;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
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
    const FORM_OPTIONS_RESOLVER = 'Symfony\Component\OptionsResolver\OptionsResolver';

    private Parser $parser;

    public function __construct(
        private Reflector $reflector,
        private QueryClient $queryClient,
        private ClientApi $clientApi,
        private Workspace $workspace,
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

        $argumentListNode = $node instanceof ArgumentExpressionList ? $node : $node->getFirstAncestor(ArgumentExpressionList::class);


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

        $formTypeClassType = $this->reflector->reflectOffset($source, $formTypeNode->getEndPosition())->nodeContext()->type();

        if (!($formTypeClassType instanceof ClassStringType)) {
            return;
        }

        $formTypeClassFQN = $formTypeClassType->className()?->full();

        if ($formTypeClassFQN === null) {
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
            if ($node instanceof QualifiedName) {
                $lhsNode = $node->getPreviousSibling();

                if (!($lhsNode instanceof StringLiteral)) {
                    return;
                }

                $option = trim($lhsNode->getText(), '\'');

                yield from $this->completeOptionRHS($formTypeClassFQN, $option);

                return;
            }
            if ($node instanceof ArrayElement) {
                $key = $node->elementKey;

                if (!($key instanceof StringLiteral)) {
                    return;
                }

                $option = trim($key->getText(), '\'');

                yield from $this->completeOptionRHS($formTypeClassFQN, $option);

                return;
            }

            if (!($node instanceof ArrayCreationExpression)) {
                return;
            }

            $arrayElementNode = $node;
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

    /**
    * @param array<string, int> $options
    * @param array<string> $visited
    */
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

            $extendsFQN = $extendsClassType->name()->full();
        }

        $priority++;

        $membersNode = $ast->getFirstDescendantNode(ClassMembersNode::class);
        $parentFQN = null;

        foreach ($membersNode?->getChildNodes() ?? [] as $member) {
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

                if ($returnExpression === null) {
                    return null;
                }

                $reflectOffset = $this->reflector->reflectOffset($source, $returnExpression->getEndPosition())->nodeContext()->type();

                if (!($reflectOffset instanceof ClassStringType)) {
                    return null;
                }

                return $reflectOffset->className()?->full();
            }
        }

        return null;
    }

    /**
    * @param array<string, int> $options
    */
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

                if ($methodName == 'setRequired') {
                    $arrayElementList = $callExpression->getFirstDescendantNode(ArrayElementList::class);
                    if (!($arrayElementList instanceof ArrayElementList)) {
                        continue;
                    }

                    $arrayElements = $arrayElementList->children;

                    foreach ($arrayElements as $arrayElement) {
                        if (!($arrayElement instanceof ArrayElement)) {
                            continue;
                        }

                        $value = $arrayElement->elementValue;

                        if (!($value instanceof StringLiteral)) {
                            continue;
                        }

                        $options[$value->getText()] = $priority;
                    }
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

    /**
    * @return Generator<FullyQualifiedName>
    */
    private function getImplementors(FullyQualifiedName $type, bool $yieldFirst): Generator
    {
        if ($yieldFirst) {
            yield $type;
        }

        foreach ($this->queryClient->class()->implementing($type) as $implementingType) {
            yield from $this->getImplementors($implementingType, true);
        }
    }

    /**
    * @return Generator<Suggestion>
    */
    private function completeFormTypes(): Generator
    {
        $fqn = FullyQualifiedName::fromString(self::FORM_TYPE_INTERFACE);

        foreach ($this->getImplementors($fqn, false) as $implementor) {
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
    /**
     * @return Generator<ReflectedClassType>
     */
    private function getAttributedClasses(FullyQualifiedName $attributeFQN): Generator
    {
        foreach ($this->queryClient->class()->referencesTo($attributeFQN) as $locationConfidence) {
            if (!$locationConfidence->isSurely()) {
                continue;
            }

            // TODO: This is way too slow, need to speed it up

            $uri = $locationConfidence->location()->uri();
            $filePath = sprintf('%s://%s', $uri->scheme(), $uri->path());
            $contents = file_get_contents($filePath);

            if (!$contents) {
                continue;
            }

            $root = $this->parser->parseSourceFile($contents) ;

            $classDeclaration = $root->getFirstDescendantNode(ClassDeclaration::class);
            if (!($classDeclaration instanceof ClassDeclaration)) {
                continue;
            }


            $attributes = $classDeclaration->attributes;

            if ($attributes === null) {
                continue;
            }

            $sourceCode = SourceCode::fromStringAndPath($contents, $filePath);

            foreach ($attributes as $attributeGroup) {
                if (count($attributeGroup->attributes->children) === 0) {
                    break;
                }

                $attributeClassReflection = $this->reflector->reflectOffset($sourceCode, $attributeGroup->startToken->getEndPosition())->nodeContext()->type();
                if ($attributeClassReflection instanceof ReflectedClassType) {
                    $attributeClassName = $attributeClassReflection->name()->full();
                    if ($attributeClassName == $attributeFQN->__toString()) {
                        $classReflection = $this->reflector->reflectOffset($sourceCode, $classDeclaration->name->getEndPosition())->nodeContext()->type();
                        if (!($classReflection instanceof ReflectedClassType)) {
                            break;
                        }

                        yield $classReflection;

                        break;
                    }
                }
            }
        }
    }
    /**
     * @return Generator<Suggestion>
     */
    private function completeOptionRHS(string $formTypeFQN, string $option): Generator
    {
        $entityClass = FullyQualifiedName::fromString('Doctrine\\ORM\\Mapping\\Entity');

        switch ($formTypeFQN) {
            case 'Symfony\\Bridge\\Doctrine\\Form\\Type\\EntityType':
                if ($option === 'class') {
                    foreach ($this->getAttributedClasses($entityClass) as $reflectedClass) {
                        $import = $reflectedClass->name()->full();
                        $name = $reflectedClass->name()->short() . '::class';

                        yield Suggestion::createWithOptions(
                            $name,
                            [
                                'label' => $name,
                                'short_description' => '',
                                'documentation' => '',
                                'class_import' => FullyQualifiedName::fromString($import),
                                'type' => Suggestion::TYPE_CLASS,
                                'priority' => Suggestion::PRIORITY_HIGH,
                            ]
                        );
                    }
                }
                break;
            default:
        };
    }

}
