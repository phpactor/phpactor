<?php

namespace Phpactor\Extension\ClassMover\Application\Logger;

use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\ClassMover\FoundReferences;
use Phpactor\Filesystem\Domain\FilePath;

class NullLogger implements ClassCopyLogger, ClassMoverLogger
{
    public function copying(FilePath $srcPath, FilePath $destPath): void
    {
    }

    public function replacing(FilePath $path, FoundReferences $references, FullyQualifiedName $replacementName): void
    {
    }

    public function moving(FilePath $srcPath, FilePath $destPath): void
    {
    }
}
