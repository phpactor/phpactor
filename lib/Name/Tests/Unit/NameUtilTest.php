<?php

namespace Phpactor\Name\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Name\NameUtil;

class NameUtilTest extends TestCase
{
    #[DataProvider('provideRelativeTo')]
    public function testRelativeTo(string $search, string $fqn, string $expected): void
    {
        self::assertEquals($expected, NameUtil::relativeToSearch($search, $fqn));
    }
    /**
     * @return Generator<array{string,string,string}>
     */
    public static function provideRelativeTo(): Generator
    {
        yield [
            'Foo',
            'Foo',
            '',
        ];
        yield [
            'Foo',
            'Foo\Bar',
            'Bar',
        ];
        yield [
            'Foo\Bar',
            'Foo\Bar',
            '',
        ];
        yield [
            'Foo\Bar\F',
            'Foo\Bar\Foobar',
            'Foobar',
        ];
        yield [
            'Foo',
            'Foo\Bar\Foobar',
            'Bar\Foobar',
        ];
    }

    /**
     * @param array{string,bool} $expected
     */
    #[DataProvider('provideSegmentAtSearch')]
    public function testSegmentAtSearch(string $fqn, string $search, array $expected): void
    {
        self::assertEquals($expected, NameUtil::childSegmentAtSearch($fqn, $search));
    }
    /**
     * @return Generator<array{string,string,array{(null|string),bool}}>
     */
    public static function provideSegmentAtSearch(): Generator
    {
        yield [
            'Foo',
            'Foo',
            [null, false],
        ];

        yield [
            'Foo\Bar',
            'Foo',
            ['Bar', true],
        ];

        yield [
            'Foo\Bar',
            'Foo\Bar',
            [null, false],
        ];
        yield [
            'Foo\Bar\Foobar',
            'Foo\Bar\F',
            ['Foobar', true],
        ];
        yield [
            'Foo\Bar\Foobar\Bar\Baz',
            'Foo\Bar',
            ['Foobar', false],
        ];
        yield [
            'Foo\Bar\Foobar\Bar\Baz',
            'Foo\Bar\\',
            ['Foobar', false],
        ];
        yield [
            'Foo\Bar\Foobar\Bar\Baz',
            'Foo',
            ['Bar', false],
        ];
        yield [
            '\Foo\Bar\Foobar\Bar\Baz',
            'Foo',
            ['Bar', false],
        ];
        yield [
            'Foo\Bar\Foobar\Bar\Baz',
            '\Foo\Bar',
            ['Foobar', false],
        ];
    }
}
