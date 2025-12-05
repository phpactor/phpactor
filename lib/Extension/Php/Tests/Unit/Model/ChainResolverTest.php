<?php

namespace Phpactor\Extension\Php\Tests\Unit\Model;

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
        $resolver = $this->createMock(PhpVersionResolver::class);
        $resolver->expects(self::once())->method('resolve')->willReturn('7.1');

        self::assertEquals('7.1', (new ChainResolver($resolver))->resolve());
    }
}
