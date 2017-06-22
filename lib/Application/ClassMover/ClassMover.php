<?php

namespace Phpactor\Application\ClassMover;

use DTL\ClassFileConverter\CompositeTransformer;
use DTL\ClassMover\ClassMover as ClassMoverFacade;
use DTL\ClassMover\Domain\FullyQualifiedName;
use DTL\Filesystem\Domain\FilePath;
use DTL\Filesystem\Domain\Filesystem;
use Phpactor\Application\ClassMover\MoveOperation;

class ClassMover
{
    private $fileClassConverter;
    private $classMover;
    private $filesystem;

    // rename compositetransformer => classToFileConverter
    public function __construct(
        CompositeTransformer $fileClassConverter,
        ClassMoverFacade $classMover,
        Filesystem $filesystem
    ) {
        $this->fileClassConverter = $fileClassConverter;
        $this->filesystem = $filesystem;
        $this->classMover = $classMover;
    }

    public function move(MoveLogger $logger, string $src, string $dest)
    {
        $operation = new MoveOperation(
            $this->fileClassConverter,
            $this->classMover,
            $this->filesystem,
            $logger
        );
        $operation->move($src, $dest);
    }
}
