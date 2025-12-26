<?php

namespace Phpactor\Filesystem\Adapter\Simple;

use Phpactor\Filesystem\Domain\Filesystem;
use Phpactor\Filesystem\Domain\FileList;
use Phpactor\Filesystem\Domain\FilePath;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Phpactor\Filesystem\Domain\FileListProvider;
use Phpactor\Filesystem\Domain\CopyReport;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Path;

class SimpleFilesystem implements Filesystem
{
    private readonly FileListProvider $fileListProvider;

    public function __construct(
        private readonly FilePath $path,
        ?FileListProvider $fileListProvider = null,
        private readonly SymfonyFilesystem $filesystem = new SymfonyFilesystem(),
    ) {
        $this->fileListProvider = $fileListProvider ?? new SimpleFileListProvider($this->path);
    }

    public function fileList(): FileList
    {
        return $this->fileListProvider->fileList();
    }

    public function remove(FilePath|string $path): void
    {
        $path = FilePath::fromFilePathOrString($path);
        $this->filesystem->remove($path);
    }

    public function move(FilePath|string $srcLocation, FilePath|string $destPath): void
    {
        $srcLocation = FilePath::fromFilePathOrString($srcLocation);
        $destPath = FilePath::fromFilePathOrString($destPath);

        $this->makeDirectoryIfNotExists((string) $destPath);
        $this->filesystem->rename($srcLocation->__toString(), $destPath->__toString());
    }

    public function copy(FilePath|string $srcLocation, FilePath|string $destPath): CopyReport
    {
        $srcLocation = FilePath::fromFilePathOrString($srcLocation);
        $destPath = FilePath::fromFilePathOrString($destPath);

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

    public function getContents(FilePath|string $path): string
    {
        $path = FilePath::fromFilePathOrString($path);
        $contents = file_get_contents($path->path());

        if (false === $contents) {
            throw new RuntimeException('Could not file_get_contents');
        }

        return $contents;
    }

    public function writeContents(FilePath|string $path, string $contents): void
    {
        $path = FilePath::fromFilePathOrString($path);
        file_put_contents($path->path(), $contents);
    }

    public function exists(FilePath|string $path): bool
    {
        $path = FilePath::fromFilePathOrString($path);
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
