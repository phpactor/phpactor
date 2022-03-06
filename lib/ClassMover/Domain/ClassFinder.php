<?php

namespace Phpactor\ClassMover\Domain;

use Phpactor\ClassMover\Domain\Reference\NamespacedClassReferences;
use Phpactor\TextDocument\TextDocument;

interface ClassFinder
{
    public function findIn(TextDocument $source): NamespacedClassReferences;
}
