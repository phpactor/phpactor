<?php

namespace Phpactor\Application\ClassMover;

use DTL\ClassFileConverter\CompositeTransformer;
use DTL\ClassMover\ClassMover as ClassMoverFacade;
use DTL\ClassMover\Domain\FullyQualifiedName;
use DTL\Filesystem\Domain\FilePath;
use DTL\Filesystem\Domain\Filesystem;
use DTL\ClassFileConverter\FilePath as ConverterFilePath;
use Phpactor\Application\ClassMover\MoveOperation;
use Phpactor\Phpactor;
use DTL\ClassFileConverter\ClassName;

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

    /**
     * Move - guess if moving by class name or file.
     */
    public function move(MoveLogger $logger, string $src, string $dest)
    {
        $srcPath = $src;
        $destPath = $dest;

        if (false === Phpactor::isFile($src)) {
            $srcPathCandidates = $this->fileClassConverter->classToFileCandidates(ClassName::fromString($src));
            if (false === $srcPathCandidates->noneFound()) {
                $srcPath = (string) $srcPathCandidates->best();
            }
        }

        if (false === Phpactor::isFile($dest)) {
            $destPathCandidates = $this->fileClassConverter->classToFileCandidates(ClassName::fromString($dest));

            if (false === $destPathCandidates->noneFound()) {
                $destPath = (string) $destPathCandidates->best();
            }
        }

        return $this->moveFile($logger, $srcPath, $destPath);
    }

    public function moveClass(MoveLogger $logger, string $srcName, string $destName)
    {
        return $this->moveFile(
            $logger,
            (string) $this->fileClassConverter->classToFileCandidates(ClassName::fromString($srcName))->best(),
            (string) $this->fileClassConverter->classToFileCandidates(ClassName::fromString($destName))->best()
        );
    }

    public function moveFile(MoveLogger $logger, string $srcPath, string $destPath)
    {
        try {
            $this->doMoveFile($logger, $srcPath, $destPath);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Could not move file "%s" to "%s"', $srcPath, $destPath), null, $e);
        }
    }

    private function doMoveFile(MoveLogger $logger, string $srcPath, string $destPath)
    {
        $srcPath = Phpactor::normalizePath($srcPath);
        $destPath = Phpactor::normalizePath($destPath);

        $srcPath = $this->filesystem->createPath($srcPath);
        $destPath = $this->filesystem->createPath($destPath);

        if (!file_exists(dirname($destPath->absolutePath()))) {
            mkdir(dirname($destPath->absolutePath()), 0777, true);
        }

        $files = [[$srcPath, $destPath]];

        if (is_dir($srcPath)) {
            $files = $this->directoryMap($srcPath, $destPath);
        }

        $this->replaceThoseReferences($logger, $files);
        $logger->moving($srcPath, $destPath);
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

    private function replaceThoseReferences(MoveLogger $logger, array $files)
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
        foreach ($this->filesystem->fileList()->phpFiles() as $filePath) {
            $references = $this->classMover->findReferences($this->filesystem->getContents($filePath), $srcName);

            $logger->replacing($filePath, $references, FullyQualifiedName::fromString($destName));

            $source = $this->classMover->replaceReferences(
                $references,
                $destName
            );

            $this->filesystem->writeContents($filePath, (string) $source);
        }
    }
}
