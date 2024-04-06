<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\CodeBuilder\Domain\Prototype\DefaultValue;

class DefaultValueTest extends TestCase
{
    /**
     * @testdox It exports values.
     * @dataProvider provideExportValues
     */
    public function testExportValues($value, $expected): void
    {
        $value = DefaultValue::fromValue($value);
        $this->assertEquals($expected, $value->export());
    }

    public function provideExportValues(): Generator
    {
        yield 'escaped string' => [
            'hello',
            '\'hello\'',
        ];
        yield 'Int' => [
            1234,
            '1234',
        ];
        yield 'It returns lowercase null' => [
            null,
            'null',
        ];
        yield 'It returns new array syntax' => [
            [],
            '[]',
        ];
        yield 'list 1' => [
            ['foobar'],
            '["foobar"]',
        ];
        yield 'list 2' => [
            ['foobar', 'bazbar'],
            '["foobar", "bazbar"]',
        ];
        yield 'array syntax 2' => [
            ['assoc' => 'foobar'],
            '["assoc" => "foobar"]',
        ];
    }
}
