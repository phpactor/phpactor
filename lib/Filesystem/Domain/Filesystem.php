<?php

namespace Phpactor\Filesystem\Domain;

use SplFileInfo;

interface Filesystem
{
    /**
     * @return FileList<SplFileInfo>
     */
    public function fileList(): FileList;

    /**
     * @param FilePath|string $srcLocation
     * @param FilePath|string $destLocation
     */
    public function move($srcLocation, $destLocation): void;

    /**
     * @param FilePath|string $location
     */
    public function remove($location): void;

    /**
     * @param FilePath|string $srcLocation
     * @param FilePath|string $destLocation
     */
    public function copy($srcLocation, $destLocation): CopyReport;

    public function createPath(string $path): FilePath;

    /**
     * @param FilePath|string $path
     */
    public function writeContents($path, string $contents): void;

    /**
     * @param FilePath|string $path
     */
    public function getContents($path): string;

    /**
     * @param FilePath|string $path
     */
    public function exists($path): bool;
}
