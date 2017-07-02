<?php

namespace Phpactor\Application;

use DTL\ClassFileConverter\Domain\ClassName;
use DTL\ClassFileConverter\Domain\ClassToFileFileToClass;
use DTL\ClassFileConverter\Domain\FilePath as ConverterFilePath;
use DTL\ClassMover\ClassMover as ClassMoverFacade;
use DTL\ClassMover\Domain\FullyQualifiedName;
use DTL\Filesystem\Domain\FilePath;
use DTL\Filesystem\Domain\Filesystem;
use Phpactor\Application\ClassMover\MoveOperation;
use Phpactor\Phpactor;
use Webmozart\Glob\Glob;
use Webmozart\PathUtil\Path;
use Phpactor\Application\Logger\ClassMoverLogger;

class ClassMover
{
    private $fileClassConverter;
    private $classMover;
    private $filesystem;

    // rename compositetransformer => classToFileConverter
    public function __construct(
        ClassToFileFileToClass $fileClassConverter,
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
    public function move(ClassMoverLogger $logger, string $src, string $dest)
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

    public function moveClass(ClassMoverLogger $logger, string $srcName, string $destName)
    {
        return $this->moveFile(
            $logger,
            (string) $this->fileClassConverter->classToFileCandidates(ClassName::fromString($srcName))->best(),
            (string) $this->fileClassConverter->classToFileCandidates(ClassName::fromString($destName))->best()
        );
    }

    public function moveFile(ClassMoverLogger $logger, string $srcPath, string $destPath)
    {
        $srcPath = Phpactor::normalizePath($srcPath);
        foreach (Glob::glob($srcPath) as $globPath) {

            $globDest = $destPath;
            // if the src is not the same as the globbed src, then it is a wildcard
            // and we want to append the filename to the destination
            if ($srcPath !== $globPath) {
                $globDest = Path::join($destPath, Path::getFilename($globPath));
            }

            try {
                $this->doMoveFile($logger, $globPath, $globDest);
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf('Could not move file "%s" to "%s"', $srcPath, $destPath), null, $e);
            }
        }
    }

    private function doMoveFile(ClassMoverLogger $logger, string $srcPath, string $destPath)
    {
        $destPath = Phpactor::normalizePath($destPath);

        $srcPath = $this->filesystem->createPath($srcPath);
        $destPath = $this->filesystem->createPath($destPath);

        if (!file_exists(dirname($destPath->path()))) {
            mkdir(dirname($destPath->path()), 0777, true);
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
            $suffix = substr($file->path(), strlen($srcPath->path()));
            $files[] = [$file->path(), $this->filesystem->createPath($destPath.$suffix)];
        }

        return $files;
    }

    private function replaceThoseReferences(ClassMoverLogger $logger, array $files)
    {
        foreach ($files as $paths) {
            list($srcPath, $destPath) = $paths;

            $srcPath = $this->filesystem->createPath($srcPath);
            $destPath = $this->filesystem->createPath($destPath);

            $srcClassName = $this->fileClassConverter->fileToClassCandidates(ConverterFilePath::fromString($srcPath->path()));
            $destClassName = $this->fileClassConverter->fileToClassCandidates(ConverterFilePath::fromString($destPath->path()));

            $this->replaceReferences($logger, $srcClassName->best()->__toString(), $destClassName->best()->__toString());
        }
    }

    private function replaceReferences(ClassMoverLogger $logger, string $srcName, string $destName)
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
