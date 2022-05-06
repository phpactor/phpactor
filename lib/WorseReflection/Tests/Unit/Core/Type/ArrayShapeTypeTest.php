<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Type;

use Generator;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayShapeType;

class ArrayShapeTypeTest extends TestCase
{
    /**
     * @dataProvider provideGeneralize
     */
    public function testGeneralize(ArrayShapeType $type, string $expected): void
    {
        self::assertEquals($expected, $type->generalize()->__toString());
    }
        
    /**
     * @return Generator<mixed>
     */
    public function provideGeneralize(): Generator
    {
        yield [
            TypeFactory::arrayShape([
                TypeFactory::stringLiteral('foo'),
                TypeFactory::stringLiteral('bar')
            ]),
            'array{string,string}',
        ];

        yield [
            TypeFactory::arrayShape([
                TypeFactory::stringLiteral('foo'),
                TypeFactory::arrayShape([
                    'foo' => TypeFactory::intLiteral(12),
                    'bar' => TypeFactory::intLiteral(12),
                ])
            ]),
            'array{string,array{foo:int,bar:int}}',
        ];
    }
}
