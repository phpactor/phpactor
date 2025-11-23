<?php

namespace Phpactor\VersionResolver\Tests;

use PHPUnit\Framework\TestCase;
use Phpactor\VersionResolver\ArbitrarySemVerResolver;
use Phpactor\VersionResolver\SemVersion;

use function Amp\Promise\wait;

class ArbitrarySemVerResolverTest extends TestCase
{
    public function testResolve(): void
    {
        $version = '1';

        $resolver = new ArbitrarySemVerResolver(new SemVersion($version));

        $version = wait($resolver->resolve());

        self::assertNotNull($version);
        self::assertSame('1', $version->__toString());
    }
}
