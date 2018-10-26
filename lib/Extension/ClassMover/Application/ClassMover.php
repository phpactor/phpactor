<?php

namespace Phpactor\Extension\ClassMover\Application;

use Phpactor\ClassMover\ClassMover as ClassMoverFacade;
use Phpactor\ClassMover\Domain\Name\FullyQualifiedName;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\Phpactor;
use Phpactor\Extension\Core\Application\Helper\ClassFileNormalizer;
use Phpactor\Filesystem\Domain\FilesystemRegistry;
use Phpactor\Extension\Core\Application\Helper\FilesystemHelper;
use Phpactor\Extension\ClassMover\Application\Logger\ClassMoverLogger;

class ClassMover
{
    /**
     * @var ClassFileNormalizer
     */
    private $classFileNormalizer;

    /**
     * @var ClassMoverFacade
     */
    private $classMover;

    /**
     * @var FilesystemRegistry
     */
    private $filesystemRegistry;

    public function __construct(
        ClassFileNormalizer $classFileNormalizer,
        ClassMoverFacade $classMover,
        FilesystemRegistry $filesystemRegistry
    ) {
        $this->classFileNormalizer = $classFileNormalizer;
        $this->filesystemRegistry = $filesystemRegistry;
        $this->classMover = $classMover;
    }

    /**
     * Move - guess if moving by class name or file.
     */
    public function move(ClassMoverLogger $logger, string $filesystemName, string $src, string $dest)
    {
        $srcPath = $this->classFileNormalizer->normalizeToFile($src);
        $destPath = $this->classFileNormalizer->normalizeToFile($dest);

        return $this->moveFile($logger, $filesystemName, $srcPath, $destPath);
    }

    public function moveClass(ClassMoverLogger $logger, string $filesystemName, string $srcName, string $destName)
    {
        return $this->moveFile(
            $logger,
            $filesystemName,
            $this->classFileNormalizer->classToFile($srcName),
            $this->classFileNormalizer->classToFile($destName)
        );
    }

    public function moveFile(ClassMoverLogger $logger, string $filesystemName, string $srcPath, string $destPath)
    {
        $srcPath = Phpactor::normalizePath($srcPath);
        foreach (FilesystemHelper::globSourceDestination($srcPath, $destPath) as $globSrc => $globDest) {
            try {
                $this->doMoveFile($logger, $filesystemName, $globSrc, $globDest);
            } catch (\Exception $e) {
                throw new \RuntimeException(sprintf('Could not move file "%s" to "%s"', $srcPath, $destPath), null, $e);
            }
        }
    }

    private function doMoveFile(ClassMoverLogger $logger, string $filesystemName, string $srcPath, string $destPath)
    {
        $filesystem = $this->filesystemRegistry->get($filesystemName);
        $destPath = Phpactor::normalizePath($destPath);

        $srcPath = $filesystem->createPath($srcPath);
        $destPath = $filesystem->createPath($destPath);

        if (!file_exists(dirname($destPath->path()))) {
            mkdir(dirname($destPath->path()), 0777, true);
        }

        $files = [[$srcPath, $destPath]];

        if (is_dir($srcPath)) {
            $files = $this->directoryMap($filesystem, $srcPath, $destPath);
        }

        $this->replaceThoseReferences($logger, $filesystem, $files);
        $logger->moving($srcPath, $destPath);
        $filesystem->move($srcPath, $destPath);
    }

    private function directoryMap(Filesystem $filesystem, FilePath $srcPath, FilePath $destPath)
    {
        $files = [];
        foreach ($filesystem->fileList()->existing()->within($srcPath)->phpFiles() as $file) {
            $suffix = substr($file->path(), strlen($srcPath->path()));
            $files[] = [$file->path(), $filesystem->createPath($destPath.$suffix)];
        }

        return $files;
    }

    private function replaceThoseReferences(ClassMoverLogger $logger, Filesystem $filesystem, array $files)
    {
        foreach ($files as $paths) {
            list($srcPath, $destPath) = $paths;

            $srcPath = $filesystem->createPath($srcPath);
            $destPath = $filesystem->createPath($destPath);

            $srcClassName = $this->classFileNormalizer->fileToClass($srcPath->path());
            $destClassName = $this->classFileNormalizer->fileToClass($destPath->path());

            $this->replaceReferences($logger, $filesystem, $srcClassName, $destClassName);
        }
    }

    private function replaceReferences(ClassMoverLogger $logger, Filesystem $filesystem, string $srcName, string $destName)
    {
        foreach ($filesystem->fileList()->existing()->phpFiles() as $filePath) {
            $references = $this->classMover->findReferences($filesystem->getContents($filePath), $srcName);

            if ($references->references()->isEmpty()) {
                continue;
            }

            $logger->replacing($filePath, $references, FullyQualifiedName::fromString($destName));

            $source = $this->classMover->replaceReferences(
                $references,
                $destName
            );

            $filesystem->writeContents($filePath, (string) $source);
        }
    }
}
