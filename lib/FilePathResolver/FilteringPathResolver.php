<?php

namespace Phpactor\FilePathResolver;

class FilteringPathResolver implements PathResolver
{
    /**
     * @var Filter[]
     */
    private array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function resolve(string $path): string
    {
        foreach ($this->filters as $filter) {
            $path = $filter->apply($path);
        }

        return $path;
    }
}
