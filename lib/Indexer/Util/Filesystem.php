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
}
