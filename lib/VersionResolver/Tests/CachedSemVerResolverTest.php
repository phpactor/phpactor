<?php

namespace Phpactor\VersionResolver\Tests;

use Amp\Success;
use PHPUnit\Framework\TestCase;
use Phpactor\VersionResolver\CachedSemVerResolver;
use Phpactor\VersionResolver\SemVersion;
use Phpactor\VersionResolver\SemVersionResolver;
use Prophecy\PhpUnit\ProphecyTrait;

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

        $cachedResolver = new CachedSemVerResolver($resolver->reveal());

        for ($i = 1; $i <= 2; $i++) {
            $actual = wait($cachedResolver->resolve());
            self::assertNotNull($actual);
            self::assertSame($version, $actual->__toString());
        }
    }
}
