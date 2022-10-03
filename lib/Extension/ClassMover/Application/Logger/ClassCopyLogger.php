<?php

namespace Phpactor\Extension\ClassMover\Application\Logger;

use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\ClassMover\FoundReferences;
use Phpactor\Filesystem\Domain\FilePath;

interface ClassCopyLogger
{
    public function copying(FilePath $srcPath, FilePath $destPath);

    public function replacing(FilePath $path, FoundReferences $references, FullyQualifiedName $replacementName);
}
