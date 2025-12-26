<?php

namespace Phpactor\Extension\ClassMover\Application;

use Phpactor\ClassMover\ClassMover as ClassMoverFacade;
use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\Phpactor;
use Symfony\Component\Filesystem\Path;
use Webmozart\Glob\Glob;
use Phpactor\Filesystem\Domain\CopyReport;
use Phpactor\Extension\Core\Application\Helper\ClassFileNormalizer;
use Phpactor\Extension\ClassMover\Application\Logger\ClassCopyLogger;
use Exception;
use RuntimeException;

class ClassCopy
{
    // rename compositetransformer => classToFileConverter
    public function __construct(
        private readonly ClassFileNormalizer $classFileNormalizer,
        private readonly ClassMoverFacade $classMover,
        private readonly Filesystem $filesystem
    ) {
    }

    /**
     * Move - guess if moving by class name or file.
     */
    public function copy(ClassCopyLogger $logger, string $src, string $dest)
    {
        $srcPath = $this->classFileNormalizer->normalizeToFile($src);
        $destPath = $this->classFileNormalizer->normalizeToFile($dest);

        return $this->copyFile($logger, $srcPath, $destPath);
    }

    public function copyClass(ClassCopyLogger $logger, string $srcName, string $destName)
    {
        return $this->copyFile(
            $logger,
            $this->classFileNormalizer->classToFile($srcName),
            $this->classFileNormalizer->classToFile($destName)
        );
    }

    public function copyFile(ClassCopyLogger $logger, string $srcPath, string $destPath): void
    {
        $srcPath = Phpactor::normalizePath($srcPath);
        if (str_ends_with($destPath, '/')) {
            $destPath = $destPath . basename($srcPath);
        }

        if (false === Glob::isDynamic($srcPath) && !file_exists($srcPath)) {
            throw new RuntimeException(sprintf(
                'File "%s" does not exist',
                $srcPath
            ));
        }

        foreach (Glob::glob($srcPath) as $globPath) {
            $globDest = $destPath;
            // if the src is not the same as the globbed src, then it is a wildcard
            // and we want to append the filename to the destination
            if ($srcPath !== $globPath) {
                $globDest = Path::join($destPath, basename($globPath));
            }

            try {
                $this->doCopyFile($logger, $globPath, $globDest);
            } catch (Exception $e) {
                throw new RuntimeException(sprintf('Could not copy file "%s" to "%s"', $srcPath, $destPath), null, $e);
            }
        }
    }

    private function doCopyFile(ClassCopyLogger $logger, string $srcPath, string $destPath): void
    {
        $destPath = Phpactor::normalizePath($destPath);

        $srcPath = $this->filesystem->createPath($srcPath);
        $destPath = $this->filesystem->createPath($destPath);

        $report = $this->filesystem->copy($srcPath, $destPath);
        $this->updateReferences($logger, $report);
        $logger->copying($srcPath, $destPath);
    }

    private function updateReferences(ClassCopyLogger $logger, CopyReport $copyReport): void
    {
        foreach ($copyReport->srcFiles() as $srcPath) {
            $destPath = $copyReport->destFiles()->current();

            $srcClassName = $this->classFileNormalizer->fileToClass($srcPath->path());
            $destClassName = $this->classFileNormalizer->fileToClass($destPath->path());

            $source = $this->filesystem->getContents($srcPath);
            $references = $this->classMover->findReferences($source, $srcClassName);
            $logger->replacing($destPath, $references, FullyQualifiedName::fromString($destClassName));
            $edits = $this->classMover->replaceReferences(
                $references,
                $destClassName
            );

            $this->filesystem->writeContents($destPath, (string) $edits->apply($source));
            $copyReport->destFiles()->next();
        }
    }
}
