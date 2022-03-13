<?php

namespace Phpactor\ClassMover;

use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\ClassMover\Domain\Reference\NamespacedClassReferences;
use Phpactor\TextDocument\TextDocument;

final class FoundReferences
{
    private $source;

    private $name;

    private $references;

    public function __construct(TextDocument $source, FullyQualifiedName $name, NamespacedClassReferences $list)
    {
        $this->source = $source;
        $this->name = $name;
        $this->references = $list;
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
