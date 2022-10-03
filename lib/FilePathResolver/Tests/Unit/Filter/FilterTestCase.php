<?php

namespace Phpactor\FilePathResolver\Tests\Unit\Filter;

use Phpactor\FilePathResolver\Filter;
use Phpactor\FilePathResolver\FilteringPathResolver;
use PHPUnit\Framework\TestCase;

abstract class FilterTestCase extends TestCase
{
    public function apply(string $path): string
    {
        return (new FilteringPathResolver([ $this->createFilter() ]))->resolve($path);
    }

    abstract protected function createFilter(): Filter;
}
