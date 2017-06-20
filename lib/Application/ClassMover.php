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
use Phpactor\Application\ClassMover\MoveLogger;
use DTL\Filesystem\Domain\Filesystem;

class ClassMover
{
    private $fileClassConverter;
    private $refReplacer;
    private $refFinder;
    private $filesystem;

    // rename compositetransformer => classToFileConverter
    public function __construct(
        CompositeTransformer $fileClassConverter,
        RefFinder $refFinder,
        RefReplacer $refReplacer,
        Filesystem $filesystem
    )
    {
        $this->fileClassConverter = $fileClassConverter;
        $this->refReplacer = $refReplacer;
        $this->refFinder = $refFinder;
        $this->filesystem = $filesystem;
    }

    public function move(MoveLogger $logger, string $srcPath, string $destPath)
    {
        if (!file_exists($srcPath)) {
            throw new \InvalidArgumentException(sprintf(
                'Source path "%s" does not exist'
            , $srcPath));
        }

        if (file_exists($destPath)) {
            throw new \InvalidArgumentException(sprintf(
                'Destination path "%s" already exists'
            , $destPath));
        }

        if (is_dir($srcPath)) {
            $files = $this->directoryMap($srcPath, $destPath);
        } else {
            $files = [ $srcPath => $destPath ];
        }

        foreach ($files as $srcPath => $destPath) {
            $srcClassName = $this->fileClassConverter->fileToClass(ConverterFilePath::fromString($srcPath));
            $destClassName = $this->fileClassConverter->fileToClass(ConverterFilePath::fromString($destPath));

            $this->replaceReferences($logger, $srcClassName->best()->__toString(), $destClassName->best()->__toString());
        }
    }

    private function moveFile(MoveLogger $logger, string $srcPath, string $destPath)
    {
        $logger->moving($srcPath, $destPath);
        $this->filesystem->move(FileLocation::fromString($srcPath), FileLocation::fromString($destPath));
    }

    private function directoryMap(string $srcPath, string $destPath)
    {
        foreach ($this->filesystem->fileList() as $file) {
            if (0 !== strpos($file->__toString(), $file->__toString())) {
                continue;
            }

            $suffix = substr($file->__toString(), strlen($srcPath));
            $files[$file->__toString()] = $destPath . $suffix;
        }

        return $files;
    }

    private function replaceReferences(MoveLogger $logger, string $srcName, string $destName)
    {
        $src = FullyQualifiedName::fromString($srcName);
        $dest = FullyQualifiedName::fromString($destName);

        foreach ($this->filesystem->fileList()->phpFiles() as $filePath) {

            $source = FileSource::fromFilePathAndString(FilePath::none(), file_get_contents($filePath));
            $logger->replacing($src, $dest, $filePath);

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
