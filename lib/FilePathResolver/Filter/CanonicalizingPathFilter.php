<?php

namespace Phpactor\FilePathResolver\Filter;

use Phpactor\FilePathResolver\Filter;
use Symfony\Component\Filesystem\Path;

class CanonicalizingPathFilter implements Filter
{
    public function apply(string $path): string
    {
        return Path::canonicalize($path);
    }
}
