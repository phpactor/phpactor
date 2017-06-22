<?php

namespace Phpactor\Application\ClassMover;

use DTL\Filesystem\Domain\FileLocation;
use DTL\Filesystem\Domain\FilePath;
use DTL\ClassMover\Domain\FullyQualifiedName;
use DTL\ClassMover\Domain\FoundReferences;

interface MoveLogger
{
    public function moving(FilePath $srcPath, FilePath $destPath);

    public function replacing(FilePath $path, FoundReferences $references, FullyQualifiedName $replacementName);
}
