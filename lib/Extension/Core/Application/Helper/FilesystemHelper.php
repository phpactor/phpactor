<?php

namespace Phpactor\Extension\Core\Application\Helper;

use Webmozart\Glob\Glob;
use Webmozart\PathUtil\Path;
use InvalidArgumentException;
use Generator;

final class FilesystemHelper
{
    public function contentsFromFileOrStdin(string $filePath): string
    {
        if (file_exists($filePath)) {
            return file_get_contents($filePath);
        }

        if ($filePath !== 'stdin') {
            throw new InvalidArgumentException(sprintf(
                'Could not locate file "%s", use "stdin" to read from STDIN',
                $filePath
            ));
        }

        $contents = '';
        while ($line = fgets(STDIN)) {
            $contents .= $line;
        }

        return $contents;
    }

    public static function globSourceDestination(string $src, string $dest): Generator
    {
        foreach (Glob::glob($src) as $globSrc) {
            $globDest = $dest;

            // if the src is not the same as the globbed src, then it is a wildcard
            // and we want to append the filename to the destination
            if ($src !== $globSrc) {
                $globDest = Path::join($dest, Path::getFilename($globSrc));
            }

            yield $globSrc => $globDest;
        }
    }
}
