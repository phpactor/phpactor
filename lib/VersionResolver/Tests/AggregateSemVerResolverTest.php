<?php

namespace Phpactor\VersionResolver\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Phpactor\VersionResolver\AggregateSemVerResolver;
use Phpactor\VersionResolver\ArbitrarySemVerResolver;
use Prophecy\PhpUnit\ProphecyTrait;

use function Amp\Promise\wait;

class AggregateSemVerResolverTest extends TestCase
{
    use ProphecyTrait;

    #[DataProvider('provideResolverData')]
    public function testResolve(?string $expected, ?string ...$componentVersions): void
    {
        $resolver = new AggregateSemVerResolver(...array_map(
            fn (?string $version) => new ArbitrarySemVerResolver($version),
            $componentVersions,
        ));

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
    public static function provideResolverData(): iterable
    {
        yield 'not null first' => ['1', '1', null];
        yield 'null first' => ['1', null, '1'];
        yield 'null only' => [null, null, null];
    }
}
