<?php

namespace Phpactor\FilePathResolver\Filter;

use Phpactor\FilePathResolver\Filter;
use Webmozart\PathUtil\Path;

class CanonicalizingPathFilter implements Filter
{
    public function apply(string $path): string
    {
        return Path::canonicalize($path);
    }
}
