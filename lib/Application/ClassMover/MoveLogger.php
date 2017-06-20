<?php

namespace Phpactor\Application\ClassMover;

use DTL\ClassMover\RefFinder\FullyQualifiedName;
use DTL\Filesystem\Domain\FileLocation;

interface MoveLogger
{
    public function moving(string $srcPath, string $destPath);

    public function replacing(FullyQualifiedName $src, FullyQualifiedName $dest, FileLocation $location);
}
