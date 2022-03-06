<?php

namespace Phpactor\FilePathResolver\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\FilePathResolver\Filter;
use Phpactor\FilePathResolver\FilteringPathResolver;
use Phpactor\FilePathResolver\PathResolver;
use Prophecy\PhpUnit\ProphecyTrait;

class FilteringPathResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testIdentity(): void
    {
        $resolver = new FilteringPathResolver();
        $this->assertInstanceOf(PathResolver::class, $resolver);
        $this->assertEquals('/foo/bar', $resolver->resolve('/foo/bar'));
    }

    public function testAppliesFilters(): void
    {
        $filter1 = $this->prophesize(Filter::class);
        $filter2 = $this->prophesize(Filter::class);

        $filter1->apply('foo')->willReturn('bar');
        $filter2->apply('bar')->willReturn('baz');

        $resolver = new FilteringPathResolver([
            $filter1->reveal(),
            $filter2->reveal()
        ]);

        $this->assertEquals('baz', $resolver->resolve('foo'));
    }
}
