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
    public function __construct(private Updater $updater)
    {
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
        $replacedImports = [];

        foreach ($classRefList as $classRef) {
            assert($classRef instanceof ClassReference);

            // Check if the class name is fully qualified and replace it with a fully qualified version of the new name
            $firstClassNameCharacter = $source->__toString()[$classRef->position()->start()];
            if ($firstClassNameCharacter === '\\') {
                $edits[] = TextEdit::create(
                    $classRef->position()->start(),
                    $classRef->position()->length(),
                    '\\'.$newName->__toString()
                );
                continue;
            }

            if (false === $importClass) {
                $importClass = $this->shouldImportClass($classRef, $originalName);
            }

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
        $source = Code::fromString($source);
        if (true === $importClass) {
            $edits = $edits->merge($this->addUseStatement($source, $newName));
        }

        if (true === $addNamespace) {
            $edits = $edits->merge($this->addNamespace($source, $newName->parentNamespace()));
        }

        return $edits;
    }

    private function replaceOriginalInstanceNamespace(NamespacedClassReferences $classRefList, FullyQualifiedName $newName): TextEdit
    {
        return TextEdit::create(
            $classRefList->namespaceRef()->position()->start(),
            $classRefList->namespaceRef()->position()->length(),
            $newName->parentNamespace()->__toString()
        );
    }

    private function shouldImportClass(ClassReference $classRef, FullyQualifiedName $originalName): bool
    {
        if ($classRef->isImport()) {
            return false;
        }
        if ($classRef->hasAlias()) {
            return false;
        }
        if (ImportedNameReference::none() != $classRef->importedNameRef()) {
            return false;
        }

        if ($classRef->isClassDeclaration()) {
            return false;
        }

        return $classRef->fullName()->equals($originalName);
    }

    private function classIsTheOriginalInstance(ClassReference $classRef, FullyQualifiedName $originalName): bool
    {
        return $classRef->isClassDeclaration() && $classRef->fullName()->equals($originalName);
    }

    private function addUseStatement(Code $source, FullyQualifiedName $newName): TextEdits
    {
        return $this->updater->textEditsFor(SourceCodeBuilder::create()->use($newName->__toString())->build(), $source);
    }

    private function addNamespace(Code $source, QualifiedName $qualifiedName): TextEdits
    {
        return $this->updater->textEditsFor(
            SourceCodeBuilder::create()->namespace($qualifiedName->__toString())->build(),
            $source
        );
    }
}
