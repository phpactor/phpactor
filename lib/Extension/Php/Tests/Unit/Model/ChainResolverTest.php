<?php

namespace Phpactor\Tests\Unit\Extension\PhpVersionResolver\Model;

use Phpactor\Extension\Php\Model\ChainResolver;
use Phpactor\Extension\Php\Model\PhpVersionResolver;
use Phpactor\TestUtils\PHPUnit\TestCase;
use RuntimeException;

class ChainResolverTest extends TestCase
{
    public function testThrowsExceptionIfNoVeresionCanBeResolved(): void
    {
        $this->expectException(RuntimeException::class);
        (new ChainResolver())->resolve();
    }

    public function testResolvesVersion(): void
    {
        $resolver = $this->prophesize(PhpVersionResolver::class);
        $resolver->resolve()->willReturn('7.1');
        self::assertEquals('7.1', (new ChainResolver($resolver->reveal()))->resolve());
    }
}
