<?php

namespace Phpactor\FilePathResolver;

class FilteringPathResolver implements PathResolver
{
    /**
     * @param Filter[] $filters
     */
    public function __construct(private readonly array $filters = [])
    {
    }

    public function resolve(string $path): string
    {
        foreach ($this->filters as $filter) {
            $path = $filter->apply($path);
        }

        return $path;
    }
}
