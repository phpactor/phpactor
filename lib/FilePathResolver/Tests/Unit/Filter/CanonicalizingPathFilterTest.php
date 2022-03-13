<?php

namespace Phpactor\FilePathResolver\Tests\Unit\Filter;

use Phpactor\FilePathResolver\Filter;
use Phpactor\FilePathResolver\Filter\CanonicalizingPathFilter;

class CanonicalizingPathFilterTest extends FilterTestCase
{
    public function testCanonicalizesThePath(): void
    {
        $this->assertEquals('/bar', $this->apply('/foo/bar/../../bar'));
    }

    protected function createFilter(): Filter
    {
        return new CanonicalizingPathFilter();
    }
}
