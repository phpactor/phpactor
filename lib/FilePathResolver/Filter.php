<?php

namespace Phpactor\FilePathResolver;

interface Filter
{
    public function apply(string $path): string;
}
