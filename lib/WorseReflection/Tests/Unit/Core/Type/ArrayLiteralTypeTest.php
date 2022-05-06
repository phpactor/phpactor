<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Type;

use Generator;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayKeyType;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\StringType;

class ArrayLiteralTypeTest extends TestCase
{
    /**
     * @dataProvider provideGeneralize
     */
    public function testGeneralize(ArrayLiteral $type, string $expected): void
    {
        self::assertEquals($expected, $type->generalize()->__toString());
    }
        
    /**
     * @return Generator<mixed>
     */
    public function provideGeneralize(): Generator
    {
        yield [
            TypeFactory::arrayLiteral([
                TypeFactory::stringLiteral('foo'),
                TypeFactory::stringLiteral('bar')
            ]),
            'array<int,string>',
        ];
        yield [
            TypeFactory::arrayLiteral([
                TypeFactory::arrayLiteral([
                    TypeFactory::stringLiteral('one'),
                    TypeFactory::stringLiteral('two'),
                ]),
                TypeFactory::stringLiteral('bar')
            ]),
            'array<int,array<int,string>>',
        ];
    }
}
