<?php

namespace Phpactor\Application\ClassMover;

use DTL\ClassFileConverter\FilePath as ConverterFilePath;
use DTL\Filesystem\Domain\FilePath;
use DTL\ClassMover\Domain\FullyQualifiedName;
use DTL\ClassFileConverter\ClassName;
use DTL\ClassFileConverter\CompositeTransformer;
use DTL\Filesystem\Domain\Filesystem;
use Phpactor\Application\ClassMover\MoveLogger;
use DTL\ClassMover\ClassMover as ClassMoverFacade;
use Phpactor\Phpactor;

final class MoveOperation
{
    protected $fileClassConverter;
    protected $filesystem;
    protected $classMover;
    protected $logger;

    public function __construct(
        CompositeTransformer $fileClassConverter,
        ClassMoverFacade $classMover,
        Filesystem $filesystem,
        MoveLogger $logger
    ) {
        $this->fileClassConverter = $fileClassConverter;
        $this->filesystem = $filesystem;
        $this->classMover = $classMover;
        $this->logger = $logger;
    }

    /**
     * Move - guess if moving by class name or file.
     */
    public function move(string $src, string $dest)
    {
        $srcPath = $src;
        $destPath = $dest;

        if (false === Phpactor::isFile($src)) {
            $srcPathCandidates = $this->fileClassConverter->classToFile(ClassName::fromString($src));
            if (false === $srcPathCandidates->noneFound()) {
                $srcPath = (string) $srcPathCandidates->best();
            }
        }

        if (false === Phpactor::isFile($dest)) {
            $destPathCandidates = $this->fileClassConverter->classToFile(ClassName::fromString($dest));

            if (false === $destPathCandidates->noneFound()) {
                $srcPath = (string) $srcPathCandidates->best();
            }
        }

        return $this->moveFile($srcPath, $destPath);
    }

    public function moveClass(string $srcName, string $destName)
    {
        return $this->moveFile(
            (string) $this->fileClassConverter->classToFile(ClassName::fromString($srcName)),
            (string) $this->fileClassConverter->classToFile(ClassName::fromString($destName))
        );
    }

    public function moveFile(string $srcPath, string $destPath)
    {
        $srcPath = $this->filesystem->createPath($srcPath);
        $destPath = $this->filesystem->createPath($destPath);

        if (!file_exists(dirname($destPath->absolutePath()))) {
            mkdir(dirname($destPath->absolutePath()), 0777, true);
        }

        $files = [[$srcPath, $destPath]];

        if (is_dir($srcPath)) {
            $files = $this->directoryMap($srcPath, $destPath);
        }

        $this->replaceThoseReferences($files);
        $this->logger->moving($srcPath, $destPath);
        $this->filesystem->move($srcPath, $destPath);
    }

    private function directoryMap(FilePath $srcPath, FilePath $destPath)
    {
        $files = [];
        foreach ($this->filesystem->fileList()->within($srcPath)->phpFiles() as $file) {
            $suffix = substr($file->absolutePath(), strlen($srcPath->absolutePath()));
            $files[] = [$file->absolutePath(), $this->filesystem->createPath($destPath.$suffix)];
        }

        return $files;
    }

    private function replaceThoseReferences(array $files)
    {
        foreach ($files as $paths) {
            list($srcPath, $destPath) = $paths;

            $srcPath = $this->filesystem->createPath($srcPath);
            $destPath = $this->filesystem->createPath($destPath);

            $srcClassName = $this->fileClassConverter->fileToClass(ConverterFilePath::fromString($srcPath->absolutePath()));
            $destClassName = $this->fileClassConverter->fileToClass(ConverterFilePath::fromString($destPath->absolutePath()));

            $this->replaceReferences($srcClassName->best()->__toString(), $destClassName->best()->__toString());
        }
    }

    private function replaceReferences(string $srcName, string $destName)
    {
        foreach ($this->filesystem->fileList()->phpFiles() as $filePath) {
            $references = $this->classMover->findReferences($this->filesystem->getContents($filePath), $srcName);

            $this->logger->replacing($filePath, $references, FullyQualifiedName::fromString($destName));

            $source = $this->classMover->replaceReferences(
                $references,
                $destName
            );

            $this->filesystem->writeContents($filePath, (string) $source);
        }
    }
}
