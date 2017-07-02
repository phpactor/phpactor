<?php

namespace Phpactor\Application\Logger;

use DTL\Filesystem\Domain\FilePath;
use DTL\ClassMover\Domain\FullyQualifiedName;
use DTL\ClassMover\Domain\FoundReferences;

interface ClassCopyLogger
{
    public function copying(FilePath $srcPath, FilePath $destPath);

    public function replacing(FilePath $path, FoundReferences $references, FullyQualifiedName $replacementName);
}
