<?php

namespace Phpactor\Application\Helper;

use DTL\Filesystem\Domain\Filesystem;

final class FilesystemHelper
{
    public function contentsFromFileOrStdin(string $filePath): string
    {
        if (file_exists($filePath)) {
            return file_get_contents($filePath);
        }

        if ($filePath !== 'stdin') {
            throw new \InvalidArgumentException(sprintf(
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
}
