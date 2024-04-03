<?php

namespace Phpactor\Indexer\Adapter\Tolerant\Indexer;

use Microsoft\PhpParser\MissingToken;
use Microsoft\PhpParser\Node;
use Microsoft\PhpParser\Node\Attribute;
use Microsoft\PhpParser\Node\Expression\ArgumentExpression;
use Microsoft\PhpParser\Node\Expression\BinaryExpression;
use Microsoft\PhpParser\Node\Expression\ScopedPropertyAccessExpression;
use Microsoft\PhpParser\Node\Statement\ClassDeclaration;
use Phpactor\Indexer\Model\Exception\CannotIndexNode;
use Phpactor\Indexer\Model\Index;
use Phpactor\Indexer\Model\Name\FullyQualifiedName;
use Phpactor\Indexer\Model\Record\ClassRecord;
use Phpactor\TextDocument\TextDocument;

class ClassDeclarationIndexer extends AbstractClassLikeIndexer
{
    public function canIndex(Node $node): bool
    {
        return $node instanceof ClassDeclaration;
    }

    public function index(Index $index, TextDocument $document, Node $node): void
    {
        assert($node instanceof ClassDeclaration);
        if ($node->name instanceof MissingToken) {
            throw new CannotIndexNode(sprintf(
                'Class name is missing (maybe a reserved word) in: %s',
                $document->uri()?->__toString() ?? '?',
            ));
        }
        $record = $this->getClassLikeRecord(ClassRecord::TYPE_CLASS, $node, $index, $document);

        $this->removeImplementations($index, $record);
        $record->clearImplemented();

        $this->indexClassInterfaces($index, $record, $node);
        $this->indexBaseClass($index, $record, $node);

        $this->indexAttributes($record, $node);

        $index->write($record);
    }

    public function indexAttributes(ClassRecord $record, ClassDeclaration $node): void
    {
        $attributes = $node->attributes ?? [];
        if (count($attributes) === 0) {
            return;
        }

        foreach ($attributes as $attributeGroup) {
            foreach ($attributeGroup->attributes->children as $attribute) {
                if (!$attribute instanceof Attribute) {
                    continue;
                }
                /** @phpstan-ignore-next-line */
                if ((string) $attribute->name?->getResolvedName() !== \Attribute::class) {
                    continue;
                }

                $targetTexts = $this->listAttributeTargetTexts($attribute);
                if ([] === $targetTexts) {
                    $record->addFlag(ClassRecord::FLAG_ATTRIBUTE);
                    return;
                }

                foreach ($targetTexts as $targetText) {
                    switch ($targetText) {
                        case 'Attribute::TARGET_CLASS':
                        case '1':
                            $record->addFlag(ClassRecord::FLAG_ATTRIBUTE_TARGET_CLASS);
                            break;
                        case 'Attribute::TARGET_FUNCTION':
                        case '2':
                            $record->addFlag(ClassRecord::FLAG_ATTRIBUTE_TARGET_FUNCTION);
                            break;
                        case 'Attribute::TARGET_METHOD':
                        case '4':
                            $record->addFlag(ClassRecord::FLAG_ATTRIBUTE_TARGET_METHOD);
                            break;
                        case 'Attribute::TARGET_PROPERTY':
                        case '8':
                            $record->addFlag(ClassRecord::FLAG_ATTRIBUTE_TARGET_PROPERTY);
                            break;
                        case 'Attribute::TARGET_CLASS_CONSTANT':
                        case '16':
                            $record->addFlag(ClassRecord::FLAG_ATTRIBUTE_TARGET_CLASS_CONSTANT);
                            break;
                        case 'Attribute::TARGET_PARAMETER':
                        case '32':
                            $record->addFlag(ClassRecord::FLAG_ATTRIBUTE_TARGET_PARAMETER);
                            break;
                        case 'Attribute::TARGET_ALL':
                        case '63':
                        default:
                            $record->addFlag(ClassRecord::FLAG_ATTRIBUTE);
                            break;
                        case 'Attribute::IS_REPEATABLE':
                        case '64':
                            $record->addFlag(ClassRecord::FLAG_ATTRIBUTE_IS_REPEATABLE);
                            break;
                    }
                }

                return;
            }
        }
    }

    private function indexClassInterfaces(Index $index, ClassRecord $classRecord, ClassDeclaration $node): void
    {
        // @phpstan-ignore-next-line because ClassInterfaceClause _can_ (and has been) be NULL
        if (null === $interfaceClause = $node->classInterfaceClause) {
            return;
        }

        if (null == $interfaceList = $interfaceClause->interfaceNameList) {
            return;
        }

        $this->indexInterfaceList($interfaceList, $classRecord, $index);
    }

    private function indexBaseClass(Index $index, ClassRecord $record, ClassDeclaration $node): void
    {
        // @phpstan-ignore-next-line because classBaseClause _can_ be NULL
        if (null === $baseClause = $node->classBaseClause) {
            return;
        }

        // @phpstan-ignore-next-line because classBaseClause _can_ be NULL
        if (null === $baseClass = $baseClause->baseClass) {
            return;
        }

        /** @phpstan-ignore-next-line */
        if ($baseClass instanceof MissingToken) {
            return;
        }

        $name = $baseClass->getResolvedName();
        $record->addImplements(FullyQualifiedName::fromString((string)$name));
        $baseClassRecord = $index->get(ClassRecord::fromName($name));
        assert($baseClassRecord instanceof ClassRecord);
        $baseClassRecord->addImplementation($record->fqn());
        $index->write($baseClassRecord);
    }

    /**
     * @return string[]
     */
    private function listAttributeTargetTexts(Node $attribute): array
    {
        $targetTexts = [];

        $isNotTarget = fn (Node $node): bool => !$node instanceof ScopedPropertyAccessExpression;

        foreach ($attribute->getDescendantNodes($isNotTarget) as $target) {
            if ($isNotTarget($target)) {
                continue;
            }

            $targetTexts[] = ltrim($target->getText(), '\\');
        }

        return $targetTexts;
    }
}
