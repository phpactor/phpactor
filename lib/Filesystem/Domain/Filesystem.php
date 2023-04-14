<?php

namespace Phpactor\Filesystem\Domain;

interface Filesystem
{
    public function fileList(): FileList;

    public function move(FilePath|string $srcLocation, FilePath|string $destLocation): void;

    public function remove(FilePath|string $location): void;

    public function copy(FilePath|string $srcLocation, FilePath|string $destLocation): CopyReport;

    public function createPath(string $path): FilePath;

    public function writeContents(FilePath|string $path, string $contents): void;

    public function getContents(FilePath|string $path): string;

    public function exists(FilePath|string $path): bool;
}
