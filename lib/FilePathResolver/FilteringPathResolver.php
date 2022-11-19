<?php

namespace Phpactor\FilePathResolver;

class FilteringPathResolver implements PathResolver
{
    /**
     * @param \Phpactor\FilePathResolver\Filter[] $filters
     */
    public function __construct(private array $filters = [])
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
