<?php

namespace Phpactor\Filesystem\Adapter\Simple;

use FilesystemIterator;
use Iterator;
use Phpactor\Filesystem\Domain\FileList;
use Phpactor\Filesystem\Domain\FileListProvider;
use Phpactor\Filesystem\Domain\FilePath;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class SimpleFileListProvider implements FileListProvider
{
    private FilePath $path;

    private bool $followSymlinks;

    public function __construct(FilePath $path, bool $followSymlinks = false)
    {
        $this->path = $path;
        $this->followSymlinks = $followSymlinks;
    }

    public function fileList(): FileList
    {
        return FileList::fromIterator(
            $this->createFileIterator(
                (string) $this->path
            )
        );
    }

    private function createFileIterator(string $path): Iterator
    {
        $path = $path ? $this->path->makeAbsoluteFromString($path) : $this->path->path();
        $flags =
            FilesystemIterator::KEY_AS_PATHNAME |
            FilesystemIterator::CURRENT_AS_FILEINFO |
            FilesystemIterator::SKIP_DOTS;

        if ($this->followSymlinks) {
            $flags = $flags | FilesystemIterator::FOLLOW_SYMLINKS;
        }

        $files = new RecursiveDirectoryIterator($path, $flags);
        $files = new RecursiveIteratorIterator(
            $files,
            RecursiveIteratorIterator::LEAVES_ONLY,
            RecursiveIteratorIterator::CATCH_GET_CHILD
        );

        return $files;
    }
}
