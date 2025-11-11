<?php

namespace Phpactor\VersionResolver\Tests;

use Amp\Success;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\VersionResolver\AggregateSemVerResolver;
use Phpactor\VersionResolver\SemVersion;
use Phpactor\VersionResolver\SemVersionResolver;
use Prophecy\PhpUnit\ProphecyTrait;

use function Amp\Promise\wait;

class AggregateSemVerResolverTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideResolverData
     */
    public function testResolve(?string $expected, ?string ...$componentVersions): void
    {
        $resolver = new AggregateSemVerResolver(...[...$this->mockResolvers(...$componentVersions)]);

        $actual = wait($resolver->resolve());

        if (null === $expected) {
            self::assertNull($actual);
            return;
        }

        self::assertNotNull($actual);

        self::assertSame($expected, $actual->__toString());
    }

    /**
     * @return iterable<array<int, string|null>>
     */
    public function provideResolverData(): iterable
    {
        yield 'not null first' => ['1', '1', null];
        yield 'null first' => ['1', null, '1'];
        yield 'null only' => [null, null, null];
    }

    /**
     * @return Generator<SemVersionResolver>
     */
    private function mockResolvers(?string ...$componentVersions): Generator
    {
        foreach ($componentVersions as $version) {
            $resolver = $this->prophesize(SemVersionResolver::class);
            $resolver
                ->resolve()
                ->willReturn(new Success((null === $version) ? null : new SemVersion($version)));

            yield $resolver->reveal();
        }
    }
}
