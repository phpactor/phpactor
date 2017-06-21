<?php

namespace Phpactor\Application;

use DTL\ClassMover\RefFinder\RefReplacer;
use DTL\ClassFileConverter\CompositeTransformer;
use DTL\ClassMover\Finder\FileSource;
use DTL\ClassMover\RefFinder\RefFinder;
use DTL\ClassFileConverter\FilePath as ConverterFilePath;
use DTL\ClassMover\Finder\FilePath as ClassMoverFilePath;
use DTL\ClassMover\RefFinder\FullyQualifiedName;
use DTL\ClassMover\Finder\Finder;
use DTL\ClassMover\Finder\SearchPath;
use Phpactor\Application\ClassMover\MoveLogger;
use DTL\Filesystem\Domain\Filesystem;
use DTL\Filesystem\Domain\FileLocation;
use Phpactor\Phpactor;
use DTL\Filesystem\Domain\FilePath;

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
        $srcPath = $this->filesystem->createPath($srcPath);
        $destPath = $this->filesystem->createPath($destPath);

        $files = [[ $srcPath, $destPath ]];
        if (is_dir($srcPath)) {
            $files = $this->directoryMap($srcPath, $destPath);
        }

        $this->replaceThoseReferences($files, $logger);
        $this->moveFile($logger, $srcPath, $destPath);
    }

    private function moveFile(MoveLogger $logger, FilePath $srcPath, FilePath $destPath)
    {
        $logger->moving($srcPath, $destPath);
        $this->filesystem->move($srcPath, $destPath);
    }

    private function directoryMap(FilePath $srcPath, FilePath $destPath)
    {
        $files = [];
        foreach ($this->filesystem->fileList()->within($srcPath)->phpFiles() as $file) {
            $suffix = substr($file->absolutePath(), strlen($srcPath->absolutePath()));
            $files[] = [$file->absolutePath(), $this->filesystem->createPath($destPath . $suffix)];
        }

        return $files;
    }

    private function replaceThoseReferences(array $files, MoveLogger $logger)
    {
        foreach ($files as $paths) {
            list($srcPath, $destPath) = $paths;

            $srcPath = $this->filesystem->createPath($srcPath);
            $destPath = $this->filesystem->createPath($destPath);

            $srcClassName = $this->fileClassConverter->fileToClass(ConverterFilePath::fromString($srcPath->absolutePath()));
            $destClassName = $this->fileClassConverter->fileToClass(ConverterFilePath::fromString($destPath->absolutePath()));

            $this->replaceReferences($logger, $srcClassName->best()->__toString(), $destClassName->best()->__toString());
        }
    }

    private function replaceReferences(MoveLogger $logger, string $srcName, string $destName)
    {
        $src = FullyQualifiedName::fromString($srcName);
        $dest = FullyQualifiedName::fromString($destName);

        foreach ($this->filesystem->fileList()->phpFiles() as $filePath) {

            $source = FileSource::fromFilePathAndString(ClassMoverFilePath::fromString($filePath), file_get_contents($filePath));
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
