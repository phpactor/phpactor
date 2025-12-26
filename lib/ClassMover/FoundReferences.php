<?php

namespace Phpactor\ClassMover;

use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\ClassMover\Domain\Reference\NamespacedClassReferences;
use Phpactor\TextDocument\TextDocument;

final class FoundReferences
{
    public function __construct(
        private readonly TextDocument $source,
        private readonly FullyQualifiedName $name,
        private readonly NamespacedClassReferences $references
    ) {
    }

    public function source(): TextDocument
    {
        return $this->source;
    }

    public function targetName(): FullyQualifiedName
    {
        return $this->name;
    }

    public function references(): NamespacedClassReferences
    {
        return $this->references;
    }
}
