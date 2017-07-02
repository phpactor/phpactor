<?php

namespace Phpactor\Application;

use DTL\ClassFileConverter\Domain\ClassName;
use DTL\ClassFileConverter\Domain\ClassToFileFileToClass;
use DTL\ClassFileConverter\Domain\FilePath as ConverterFilePath;
use DTL\ClassMover\ClassMover as ClassMoverFacade;
use DTL\ClassMover\Domain\FullyQualifiedName;
use DTL\Filesystem\Domain\FilePath;
use DTL\Filesystem\Domain\Filesystem;
use Phpactor\Application\ClassCopy\MoveOperation;
use Phpactor\Phpactor;
use Webmozart\Glob\Glob;
use Webmozart\PathUtil\Path;
use Phpactor\Application\Logger\ClassCopyLogger;
use DTL\Filesystem\Domain\CopyReport;

class ClassCopy
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
    public function copy(ClassCopyLogger $logger, string $src, string $dest)
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

        return $this->copyFile($logger, $srcPath, $destPath);
    }

    public function copyClass(ClassCopyLogger $logger, string $srcName, string $destName)
    {
        return $this->copyFile(
            $logger,
            (string) $this->fileClassConverter->classToFileCandidates(ClassName::fromString($srcName))->best(),
            (string) $this->fileClassConverter->classToFileCandidates(ClassName::fromString($destName))->best()
        );
    }

    public function copyFile(ClassCopyLogger $logger, string $srcPath, string $destPath)
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
                $this->doCopyFile($logger, $globPath, $globDest);
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf('Could not copy file "%s" to "%s"', $srcPath, $destPath), null, $e);
            }
        }
    }

    private function doCopyFile(ClassCopyLogger $logger, string $srcPath, string $destPath)
    {
        $destPath = Phpactor::normalizePath($destPath);

        $srcPath = $this->filesystem->createPath($srcPath);
        $destPath = $this->filesystem->createPath($destPath);

        $report = $this->filesystem->copy($srcPath, $destPath);
        $this->updateReferences($logger, $report);
        $logger->copying($srcPath, $destPath);
    }

    private function updateReferences(ClassCopyLogger $logger, CopyReport $copyReport)
    {
        foreach ($copyReport->srcFiles() as $srcPath) {
            $destPath = $copyReport->destFiles()->current();

            $srcClassName = $this->fileClassConverter->fileToClassCandidates(
                ConverterFilePath::fromString($srcPath->path())
            )->best();
            $destClassName = $this->fileClassConverter->fileToClassCandidates(
                ConverterFilePath::fromString($destPath->path())
            )->best();

            $references = $this->classMover->findReferences($this->filesystem->getContents($srcPath), $srcClassName);
            $logger->replacing($destPath, $references, FullyQualifiedName::fromString($destClassName));
            $source = $this->classMover->replaceReferences(
                $references,
                $destClassName
            );

            $this->filesystem->writeContents($destPath, (string) $source);
            $copyReport->destFiles()->next();
        }
    }
}
