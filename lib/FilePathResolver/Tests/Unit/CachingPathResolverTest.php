<?php

namespace Phpactor\FilePathResolver\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\FilePathResolver\CachingPathResolver;
use Phpactor\FilePathResolver\PathResolver;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class CachingPathResolverTest extends TestCase
{
    use ProphecyTrait;
    
    /**
     * @var ObjectProphecy<PathResolver>
     */
    private ObjectProphecy $resolver;

    public function setUp(): void
    {
        $this->resolver = $this->prophesize(PathResolver::class);
    }

    public function testCachesResult(): void
    {
        $caching = new CachingPathResolver($this->resolver->reveal());
        $this->resolver->resolve('foo')->willReturn('bar')->shouldBeCalledOnce();

        $caching->resolve('foo');
        $caching->resolve('foo');
        $caching->resolve('foo');
        $this->assertEquals('bar', $caching->resolve('foo'));
    }
}
