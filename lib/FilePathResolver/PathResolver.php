<?php

namespace Phpactor\FilePathResolver;

interface PathResolver
{
    public function resolve(string $path): string;
}
