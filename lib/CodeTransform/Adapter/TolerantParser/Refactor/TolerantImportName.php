<?php

namespace Phpactor\CodeTransform\Adapter\TolerantParser\Refactor;

use Microsoft\PhpParser\ClassLike;
use Microsoft\PhpParser\NamespacedNameInterface;
use Microsoft\PhpParser\Node\SourceFileNode;
use Phpactor\CodeTransform\Domain\Refactor\ImportClass\NameImport;
use Phpactor\CodeTransform\Domain\Refactor\ImportName;
use Microsoft\PhpParser\Parser;
use Phpactor\CodeTransform\Domain\SourceCode;
use Microsoft\PhpParser\Node\QualifiedName;
use Phpactor\CodeTransform\Domain\ClassName;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Microsoft\PhpParser\Node;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\Name\FullyQualifiedName;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\TextEdits;

class TolerantImportName implements ImportName
{
    private Parser $parser;

    public function __construct(
        private Updater $updater,
        Parser $parser = null,
        private bool $importGlobals = false
    ) {
        $this->parser = $parser ?: new Parser();
    }

    public function importName(SourceCode $source, ByteOffset $offset, NameImport $nameImport): TextEdits
    {
        if ($this->importGlobals === false && $nameImport->isGlobalFunction()) {
            return TextEdits::none();
        }

        $sourceNode = $this->parser->parseSourceFile($source);
        $node = $this->getLastNodeAtPosition($sourceNode, $offset);

        $edits = $this->addImport($source, $nameImport);

        //if ($nameImport->alias() !== null) {
            //$edits = $this->updateReferences($node, $nameImport, $edits);
        //}

        return $edits;
    }

    public function importNameOnly(SourceCode $source, ByteOffset $offset, NameImport $nameImport): TextEdits
    {
        if ($this->importGlobals === false && $nameImport->isGlobalFunction()) {
            return TextEdits::none();
        }

        $sourceNode = $this->parser->parseSourceFile($source);
        $node = $this->getLastNodeAtPosition($sourceNode, $offset);

        return $this->addImport($source, $nameImport);
    }

    private function currentClassIsSameAsImportClass(Node $node, FullyQualifiedName $className): bool
    {
        if (!$node instanceof ClassLike || !$node instanceof NamespacedNameInterface) {
            return false;
        }

        if ((string) $node->getNamespacedName() === (string) $className) {
            return true;
        }

        return false;
    }

    private function addImport(SourceCode $source, NameImport $nameImport): TextEdits
    {
        $builder = SourceCodeBuilder::create();
        $builder->useName($nameImport);
        $prototype = $builder->build();

        return $this->updater->textEditsFor($prototype, Code::fromString((string) $source));
    }

    private function importClassInSameNamespace(Node $node, FullyQualifiedName $className): bool
    {
        $namespace = '';
        if ($definition = $node->getNamespaceDefinition()) {
            $namespace = (string) $definition->getFirstChildNode(QualifiedName::class);
        }

        if ($className->count() > 1 && $className->tail()->__toString() == $namespace) {
            return true;
        }

        return false;
    }

    //private function updateReferences(Node $node, NameImport $nameImport, TextEdits $edits): TextEdits
    //{
        //$alias = $nameImport->alias();

        //if (is_null($alias)) {
            //return $edits;
        //}

        //return $edits->add(TextEdit::create(
            //$node->getStartPosition(),
            //$node->getEndPosition() - $node->getStartPosition(),
            //$alias
        //));
    //}

    private function currentClass(Node $node): ?ClassName
    {
        $classDeclaration = $node->getFirstAncestor(ClassLike::class);

        if (!$classDeclaration instanceof NamespacedNameInterface) {
            return null;
        }


        $name = (string)$classDeclaration->getNamespacedName();

        if (!$name) {
            return null;
        }

        return ClassName::fromString($name);
    }

    private function resolveImportTableOffset(NameImport $nameImport): int
    {
        return $nameImport->isFunction() ? 1 : 0;
    }

    private function getLastNodeAtPosition(SourceFileNode $sourceNode, ByteOffset $offset): Node
    {
        $node = $sourceNode->getDescendantNodeAtPosition($offset->toInt());

        /*
         * In case the cursor is not on a recognized node we need to find the
         * first available node after the cusror in order to make sure the
         * import table will be loaded.
         */
        if ($node instanceof SourceFileNode) {
            /** @var Node $childNode */
            foreach ($node->getChildNodes() as $childNode) {
                if ($childNode->getStartPosition() > $offset->toInt()) {
                    break;
                }

                $node = $childNode;
            }
        }

        return $node;
    }
}
