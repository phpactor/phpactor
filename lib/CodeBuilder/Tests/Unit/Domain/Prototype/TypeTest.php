<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Prototype\Type;

class TypeTest extends TestCase
{
    /**
     * @dataProvider provideNamespace
     */
    public function testItReturnsANamespace(string $classFqn, string $expectedNamespace = null): void
    {
        $type = Type::fromString($classFqn);
        $this->assertEquals($expectedNamespace, $type->namespace());
    }

    public function provideNamespace()
    {
        yield [
            'Foo\\Bar',
            'Foo',
        ];

        yield [
            'Foo\\Bar\\Zoo',
            'Foo\\Bar',
        ];

        yield [
            'Foo\\Bar\\Zoo\\Zog',
            'Foo\\Bar\\Zoo',
        ];

        yield [
            '?Foo\\Bar',
            'Foo',
        ];

        yield [
            '?Bar',
            null
        ];

        yield [
            'Bar',
            null
        ];

        yield [
            '',
            null
        ];
    }
}
