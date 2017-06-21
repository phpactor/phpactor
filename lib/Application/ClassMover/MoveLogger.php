<?php

namespace Phpactor\Application\ClassMover;

use DTL\ClassMover\RefFinder\FullyQualifiedName;
use DTL\Filesystem\Domain\FileLocation;
use DTL\Filesystem\Domain\FilePath;

interface MoveLogger
{
    public function moving(FilePath $srcPath, FilePath $destPath);

    public function replacing(FullyQualifiedName $src, FullyQualifiedName $dest, FilePath $path);
}
