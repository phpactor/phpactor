<?php

namespace Phpactor\ClassMover\Adapter\TolerantParser;

use Phpactor\ClassMover\Domain\Name\QualifiedName;
use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\ClassMover\Domain\Reference\NamespacedClassReferences;
use Phpactor\ClassMover\Domain\ClassReplacer;
use Phpactor\ClassMover\Domain\Reference\ImportedNameReference;
use Phpactor\ClassMover\Domain\Reference\ClassReference;
use Phpactor\CodeBuilder\Domain\Builder\SourceCodeBuilder;
use Phpactor\CodeBuilder\Domain\Code;
use Phpactor\CodeBuilder\Domain\Updater;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdit;
use Phpactor\TextDocument\TextEdits;

class TolerantClassReplacer implements ClassReplacer
{
    /**
     * @var Updater
     */
    private $updater;

    public function __construct(Updater $updater)
    {
        $this->updater = $updater;
    }

    public function replaceReferences(
        TextDocument $source,
        NamespacedClassReferences $classRefList,
        FullyQualifiedName $originalName,
        FullyQualifiedName $newName
    ): TextEdits {
        $edits = [];
        $importClass = false;
        $addNamespace = false;

        foreach ($classRefList as $classRef) {
            $importClass = $this->shouldImportClass($classRef, $originalName);

            if ($this->classIsTheOriginalInstance($classRef, $originalName)) {
                $addNamespace = $classRefList->namespaceRef()->namespace()->isRoot();

                if (false === $addNamespace) {
                    $edits[] = $this->replaceOriginalInstanceNamespace($classRefList, $newName);
                }
            }

            $edits[] = TextEdit::create(
                $classRef->position()->start(),
                $classRef->position()->length(),
                $classRef->name()->transpose($newName)->__toString()
            );
        }

        // make sure the edits are ordered
        usort($edits, function (TextEdit $a, TextEdit $b) {
            return $a->start()->toInt() <=> $b->start()->toInt();
        });

        $edits = TextEdits::fromTextEdits($edits);
        if (true === $importClass) {
            $edits = $edits->merge($this->addUseStatement($source, $newName));
        }

        if (true === $addNamespace) {
            $edits = $edits->merge($this->addNamespace($source, $newName->parentNamespace()));
        }

        return $edits;
    }

    private function replaceOriginalInstanceNamespace(NamespacedClassReferences $classRefList, FullyQualifiedName $newName)
    {
        return TextEdit::create(
            $classRefList->namespaceRef()->position()->start(),
            $classRefList->namespaceRef()->position()->length(),
            $newName->parentNamespace()->__toString()
        );
    }

    private function shouldImportClass(ClassReference $classRef, FullyQualifiedName $originalName)
    {
        return ImportedNameReference::none() == $classRef->importedNameRef() &&
            false === ($classRef->isClassDeclaration() && $classRef->fullName()->equals($originalName));
    }

    private function classIsTheOriginalInstance($classRef, $originalName)
    {
        return $classRef->isClassDeclaration() && $classRef->fullName()->equals($originalName);
    }

    private function addUseStatement(TextDocument $source, FullyQualifiedName $newName): TextEdits
    {
        return $this->updater->textEditsFor(SourceCodeBuilder::create()->use($newName->__toString())->build(), Code::fromString($source->__toString()));
    }

    private function addNamespace(TextDocument $source, QualifiedName $qualifiedName): TextEdits
    {
        return $this->updater->textEditsFor(SourceCodeBuilder::create()->namespace($qualifiedName->__toString())->build(), Code::fromString($source->__toString()));
    }
}
