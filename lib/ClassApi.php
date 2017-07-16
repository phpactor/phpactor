<?php

namespace Phpactor;

use Phpactor\Application\Helper\ClassFileNormalizer;
use Phpactor\ClassMover\ClassMover;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\Application\Logger\ClassCopyLogger;

interface ClassApi
{
    /**
     * // rename compositetransformer =&gt; classToFileConverter
     */
    public function __construct(ClassFileNormalizer $classFileNormalizer, ClassMover $classMover, Filesystem $filesystem);

    /**
     * Move - guess if moving by class name or file.
     */
    public function copy(ClassCopyLogger $logger, string $src, string $dest);

    /**
     * 
     */
    public function copyClass(ClassCopyLogger $logger, string $srcName, string $destName);

    /**
     * 
     */
    public function copyFile(ClassCopyLogger $logger, string $srcPath, string $destPath);
}