<?php

namespace Phpactor\Application;

use DTL\ClassMover\RefFinder\RefReplacer;
use DTL\ClassFileConverter\CompositeTransformer;
use DTL\ClassMover\Finder\FileSource;
use DTL\ClassMover\RefFinder\RefFinder;
use DTL\ClassFileConverter\FilePath as ConverterFilePath;
use DTL\ClassMover\Finder\FilePath;
use DTL\ClassMover\RefFinder\FullyQualifiedName;
use DTL\ClassMover\Finder\Finder;
use DTL\ClassMover\Finder\SearchPath;

class ClassMover
{
    private $fileClassConverter;
    private $refReplacer;
    private $refFinder;
    private $fileFinder;

    // rename compositetransformer => classToFileConverter
    public function __construct(
        CompositeTransformer $fileClassConverter,
        RefFinder $refFinder,
        RefReplacer $refReplacer,
        Finder $finder
    )
    {
        $this->fileClassConverter = $fileClassConverter;
        $this->refReplacer = $refReplacer;
        $this->refFinder = $refFinder;
        $this->fileFinder = $finder;
    }

    public function move(string $srcPath, string $destPath, array $refSearchPaths)
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
            $files = $this->moveDirectory($srcPath, $destPath);
        } else {
            $files = $this->moveFile($srcPath, $destPath);
        }

        foreach ($files as $srcPath => $destPath) {
            $srcClassName = $this->fileClassConverter->fileToClass(ConverterFilePath::fromString($srcPath));
            $destClassName = $this->fileClassConverter->fileToClass(ConverterFilePath::fromString($destPath));

            $this->replaceReferences($srcClassName->best()->__toString(), $destClassName->best()->__toString(), $refSearchPaths);
        }
    }

    private function moveFile(string $srcPath, string $destPath)
    {
        // move file
        rename($srcPath, $destPath);

        return [ $srcPath => $destPath ];
    }

    private function replaceReferences(string $srcName, string $destName, array $searchPaths)
    {
        $src = FullyQualifiedName::fromString($srcName);
        $dest = FullyQualifiedName::fromString($destName);

        foreach ($searchPaths as $searchPath) {
            foreach ($this->fileFinder->findIn(SearchPath::fromString($searchPath)) as $filePath) {

                $source = FileSource::fromFilePathAndString(FilePath::fromString($filePath), file_get_contents($filePath));

                $refList = $this->refFinder->findIn($source)->filterForName($src);

                $source = $this->refReplacer->replaceReferences(
                    $source,
                    $refList,
                    $src,
                    $dest
                );

                $source->writeBackToFile();
            }
        }
    }
}
