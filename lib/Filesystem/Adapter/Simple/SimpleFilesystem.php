<?php

namespace Phpactor\Filesystem\Adapter\Simple;

use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\Filesystem\Domain\FileList;
use Phpactor\Filesystem\Domain\FilePath;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Webmozart\PathUtil\Path;
use Phpactor\Filesystem\Domain\FileListProvider;
use Phpactor\Filesystem\Domain\CopyReport;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class SimpleFilesystem implements Filesystem
{
    private FilePath $path;

    private FileListProvider $fileListProvider;

    private SymfonyFilesystem $filesystem;

    /**
     * @param FilePath|string $path
     */
    public function __construct($path, ?FileListProvider $fileListProvider = null, ?SymfonyFilesystem $filesystem = null)
    {
        $this->path = FilePath::fromUnknown($path);
        $this->fileListProvider = $fileListProvider ?: new SimpleFileListProvider($this->path);
        $this->filesystem = $filesystem ?: new SymfonyFilesystem();
    }

    public function fileList(): FileList
    {
        return $this->fileListProvider->fileList();
    }

    public function remove($path): void
    {
        $path = FilePath::fromUnknown($path);
        $this->filesystem->remove($path);
    }

    public function move($srcLocation, $destPath): void
    {
        $srcLocation = FilePath::fromUnknown($srcLocation);
        $destPath = FilePath::fromUnknown($destPath);

        $this->makeDirectoryIfNotExists((string) $destPath);
        $this->filesystem->rename($srcLocation->__toString(), $destPath->__toString());
    }

    public function copy($srcLocation, $destPath): CopyReport
    {
        $srcLocation = FilePath::fromUnknown($srcLocation);
        $destPath = FilePath::fromUnknown($destPath);

        if ($srcLocation->isDirectory()) {
            return $this->copyDirectory($srcLocation, $destPath);
        }

        $this->makeDirectoryIfNotExists((string) $destPath);
        $this->filesystem->copy($srcLocation->__toString(), $destPath->__toString());

        return CopyReport::fromSrcAndDestFiles(
            FileList::fromFilePaths([ $srcLocation ]),
            FileList::fromFilePaths([ $destPath ])
        );
    }

    public function createPath(string $path): FilePath
    {
        if (Path::isRelative($path)) {
            return FilePath::fromParts([$this->path->path(), $path]);
        }

        return FilePath::fromString($path);
    }

    public function getContents($path): string
    {
        $path = FilePath::fromUnknown($path);
        $contents = file_get_contents($path->path());

        if (false === $contents) {
            throw new RuntimeException('Could not file_get_contents');
        }

        return $contents;
    }

    public function writeContents($path, string $contents): void
    {
        $path = FilePath::fromUnknown($path);
        file_put_contents($path->path(), $contents);
    }

    public function exists($path): bool
    {
        $path = FilePath::fromUnknown($path);
        return file_exists($path);
    }

    private function makeDirectoryIfNotExists(string $destPath): void
    {
        if (file_exists(dirname($destPath))) {
            return;
        }

        $this->filesystem->mkdir(dirname($destPath), 0777);
    }

    private function copyDirectory(FilePath $srcLocation, FilePath $destPath): CopyReport
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($srcLocation->path(), RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $destFiles = [];
        $srcFiles = [];
        foreach ($iterator as $file) {
            $filePath = $destPath->path() . '/' . $iterator->getSubPathName();
            if ($file->isDir()) {
                continue;
            }

            $this->filesystem->copy($file, $filePath);

            $srcFiles[] = FilePath::fromString($file);
            $destFiles[] = FilePath::fromString($filePath);
        }

        return CopyReport::fromSrcAndDestFiles(FileList::fromFilePaths($srcFiles), FileList::fromFilePaths($destFiles));
    }
}
