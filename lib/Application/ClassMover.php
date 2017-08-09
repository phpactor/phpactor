<?php

namespace Phpactor\Application;

use Phpactor\ClassFileConverter\Domain\ClassToFileFileToClass;
use Phpactor\ClassMover\ClassMover as ClassMoverFacade;
use Phpactor\ClassMover\Domain\FullyQualifiedName;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\Phpactor;
use Webmozart\Glob\Glob;
use Webmozart\PathUtil\Path;
use Phpactor\Application\Logger\ClassMoverLogger;
use Phpactor\Application\Helper\ClassFileNormalizer;

class ClassMover
{
    /**
     * @var ClassToFileFileToClass
     */
    private $classFileNormalizer;

    /**
     * @var ClassMoverFacade
     */
    private $classMover;

    /**
     * @var Filesystem
     */
    private $filesystem;

    // rename compositetransformer => classToFileConverter
    public function __construct(
        ClassFileNormalizer $classFileNormalizer,
        ClassMoverFacade $classMover,
        Filesystem $filesystem
    ) {
        $this->classFileNormalizer = $classFileNormalizer;
        $this->filesystem = $filesystem;
        $this->classMover = $classMover;
    }

    /**
     * Move - guess if moving by class name or file.
     */
    public function move(ClassMoverLogger $logger, string $src, string $dest)
    {
        $srcPath = $this->classFileNormalizer->normalizeToFile($src);
        $destPath = $this->classFileNormalizer->normalizeToFile($dest);

        return $this->moveFile($logger, $srcPath, $destPath);
    }

    public function moveClass(ClassMoverLogger $logger, string $srcName, string $destName)
    {
        return $this->moveFile(
            $logger,
            $this->classFileNormalizer->classToFile($srcName),
            $this->classFileNormalizer->classToFile($destName)
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

            $srcClassName = $this->classFileNormalizer->fileToClass($srcPath->path());
            $destClassName = $this->classFileNormalizer->fileToClass($destPath->path());

            $this->replaceReferences($logger, $srcClassName, $destClassName);
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
