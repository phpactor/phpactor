<?php

namespace Phpactor\Name\Tests\Unit;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\Name\NameUtil;

class NameUtilTest extends TestCase
{
    /**
     * @dataProvider provideRelativeTo
     */
    public function testRelativeTo(string $search, string $fqn, string $expected): void
    {
        self::assertEquals($expected, NameUtil::relativeToSearch($search, $fqn));
    }
    /**
     * @return Generator<array{string,string,string}>
     */
    public function provideRelativeTo(): Generator
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
     * @dataProvider provideSegmentAtSearch
     * @param array{string,bool} $expected
     */
    public function testSegmentAtSearch(string $fqn, string $search, array $expected): void
    {
        self::assertEquals($expected, NameUtil::childSegmentAtSearch($fqn, $search));
    }
    /**
     * @return Generator<array{string,string,string}>
     */
    public function provideSegmentAtSearch(): Generator
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
    }
}
