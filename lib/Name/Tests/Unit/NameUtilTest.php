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
}
