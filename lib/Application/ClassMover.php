<?php

namespace Phpactor\Application;

use DTL\ClassMover\RefFinder\RefReplacer;
use DTL\ClassFileConverter\FilePath;
use DTL\ClassFileConverter\CompositeTransformer;
use DTL\ClassMover\Finder\FileSource;
use DTL\ClassMover\RefFinder\RefFinder;

class ClassMover
{
    private $fileClassConverter;
    private $refReplacer;
    private $refFinder;

    // rename compositetransformer => classToFileConverter
    public function __construct(
        CompositeTransformer $fileClassConverter,
        RefFinder $refFinder,
        RefReplacer $refReplacer
    )
    {
        $this->fileClassConverter = $fileClassConverter;
        $this->refReplacer = $refReplacer;
    }

    public function move(string $srcPath, string $destPath)
    {
        if (!file_exists($srcPath)) {
            throw new \InvalidArgumentException(sprintf(
                'Source path "%s" does not exist'
            ), $srcPath);
        }

        if (file_exists($destPath)) {
            throw new \InvalidArgumentException(sprintf(
                'Destination path "%s" already exists'
            ), $destPath);
        }

        if (is_dir($srcPath)) {
            $this->moveDirectory($srcPath, $destPath);
        }

        $this->moveFile($srcPath, $destPath);
    }

    private function moveFile(string $srcPath, string $destPath)
    {
        $currentClassName = $this->fileClassConverter->fileToClass(FilePath::fromString($srcPath));
        $newClassName = $this->fileClassConverter->fileToClass(FilePath::fromString($srcPath));

        $source = FileSource::fromString(file_get_contents($srcPath));
        $refList = $this->refFinder->findIn($source)->filterForName(
            FullyQualifiedName::fromString($currentClassName->__toString())
        );

        $source = $this->refReplacer->replaceReferences(
            $source,
            FullyQualifiedName::fromString($currentClassName->__toString()),
            FullyQualifiedName($newClassName->__toString())
        );
        file_put_contents($destPath, $source->__toString());
        unlink($srcPath);
    }
}
