<?php

namespace Phpactor\CodeBuilder\Tests\Unit\Domain\Prototype;

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

    public function provideExportValues()
    {
        return [
            [
                'hello',
                '\'hello\'',
            ],
            [
                1234,
                '1234',
            ],
            'It returns lowercase null' => [
                null,
                'null',
            ],
            'It returns new array syntax' => [
                [],
                '[]',
            ],
            'list 1' => [
                ['foobar'],
                '["foobar"]',
            ],
            'list 2' => [
                ['foobar', 'bazbar'],
                '["foobar", "bazbar"]',
            ],
            'array syntax 2' => [
                ['assoc' => 'foobar'],
                '["assoc" => "foobar"]',
            ]
        ];
    }
}
