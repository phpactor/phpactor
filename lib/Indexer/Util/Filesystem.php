<?php

namespace Phpactor\Indexer\Util;

use RecursiveDirectoryIterator;

use RecursiveIteratorIterator;
use SplFileInfo;

class Filesystem
{
    public static function removeDir(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        if ($path) {
            $splFileInfo = new SplFileInfo($path);

            if (in_array($splFileInfo->getType(), ['socket', 'file', 'link'])) {
                unlink($path);
                return;
            }
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            self::removeDir($file->getPathName());
        }

        rmdir($path);
    }

    public static function formatSize(int $byteCount): string
    {
        if ($byteCount === 0) {
            return '0';
        }
        $unitIndex = floor(log($byteCount, 1024));
        $units = ['', 'K', 'M', 'G', 'T', 'P'];

        return sprintf('%.2f %s', $byteCount / pow(1024, $unitIndex), $units[$unitIndex]);
    }

    /**
     * Returns the size of a path (recursively) and returns the size of the path in bytes.
     */
    public static function sizeOfPath(string $path): int
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        $size = 0;
        foreach ($files as $file) {
            $size += $file->getSize();
        }

        return $size;
    }
}
