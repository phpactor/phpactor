<?php

namespace Phpactor\VersionResolver\Tests;

use Amp\Success;
use PHPUnit\Framework\TestCase;
use Phpactor\VersionResolver\CachedSemVerResolver;
use Phpactor\VersionResolver\SemVersion;
use Phpactor\VersionResolver\SemVersionResolver;
use Prophecy\PhpUnit\ProphecyTrait;

use Psr\Log\LoggerInterface;
use function Amp\Promise\wait;

class CachedSemVerResolverTest extends TestCase
{
    use ProphecyTrait;

    public function testResolve(): void
    {
        $version = '1';
        $resolver = $this->prophesize(SemVersionResolver::class);
        $resolver
            ->resolve()
            ->willReturn(new Success(SemVersion::fromString($version)))
            ->shouldBeCalledOnce()
        ;

        $logger = $this->prophesize(LoggerInterface::class);
        $logger->info('resolved version "1"')->shouldBeCalledOnce();

        $cachedResolver = new CachedSemVerResolver(
            $resolver->reveal(),
            $logger->reveal(),
        );

        for ($i = 1; $i <= 2; $i++) {
            $actual = wait($cachedResolver->resolve());
            self::assertNotNull($actual);
            self::assertSame($version, $actual->__toString());
        }
    }
}
