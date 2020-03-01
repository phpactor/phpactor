<?php

namespace Phpactor\Tests\Unit\Extension\PhpVersionResolver\Model;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Php\Model\ChainResolver;
use Phpactor\Extension\Php\Model\PhpVersionResolver;
use RuntimeException;

class ChainResolverTest extends TestCase
{
    public function testThrowsExceptionIfNoVeresionCanBeResolved()
    {
        $this->expectException(RuntimeException::class);
        (new ChainResolver())->resolve();
    }

    public function testResolvesVersion()
    {
        $resolver = $this->prophesize(PhpVersionResolver::class);
        $resolver->resolve()->willReturn('7.1');
        self::assertEquals('7.1', (new ChainResolver($resolver->reveal()))->resolve());
    }
}
