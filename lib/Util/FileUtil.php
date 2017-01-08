<?php

namespace Phpactor\Util;

class FileUtil
{
    public static function assertExists(string $file)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException(sprintf(
                'File "%s" does not exist', $file
            ));
        }
    }
}
