<?php

namespace Phpactor\Application\ClassMover;

use DTL\ClassMover\Finder\FilePath;
use DTL\ClassMover\RefFinder\FullyQualifiedName;

interface MoveLogger
{
    public function moving(string $srcPath, string $destPath);

    public function replacing(FullyQualifiedName $src, FullyQualifiedName $dest, FilePath $path);
}
