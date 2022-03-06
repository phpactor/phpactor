<?php

namespace Phpactor\ClassMover\Domain;

use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\ClassMover\Domain\Reference\NamespacedClassReferences;
use Phpactor\TextDocument\TextDocument;
use Phpactor\TextDocument\TextEdits;

interface ClassReplacer
{
    public function replaceReferences(TextDocument $source, NamespacedClassReferences $classRefList, FullyQualifiedName $originalName, FullyQualifiedName $newName): TextEdits;
}
