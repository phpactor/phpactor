<?php

namespace Phpactor;

class Phpactor
{
    /**
     * If the path is relative we need to use the current working path
     * because otherwise it will be the script path, which is wrong in the
     * context of a PHAR.
     *
     * @param string $path
     *
     * @return string
     */
    public static function normalizePath($path)
    {
        if (substr($path, 0, 1) == DIRECTORY_SEPARATOR) {
            return $path;
        }

        return getcwd() . DIRECTORY_SEPARATOR . $path;
    }
}
