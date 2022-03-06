<?php

namespace Phpactor\FilePathResolver\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\FilePathResolver\LoggingPathResolver;
use Phpactor\FilePathResolver\PathResolver;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;

class LoggingPathResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testLogsResolvedPath(): void
    {
        $innerResolver = $this->prophesize(PathResolver::class);
        $logger = $this->prophesize(LoggerInterface::class);
        $innerResolver->resolve('foo')->willReturn('bar');

        $resolver = new LoggingPathResolver(
            $innerResolver->reveal(),
            $logger->reveal()
        );

        $this->assertEquals(
            'bar',
            $resolver->resolve('foo')
        );

        $logger->log('debug', 'Resolved path "foo" to "bar"')->shouldHaveBeenCalled();
    }
}
