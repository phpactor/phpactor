<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayLiteral;

class ArrayLiteralTypeTest extends TestCase
{
    #[DataProvider('provideGeneralize')]
    public function testGeneralize(ArrayLiteral $type, string $expected): void
    {
        self::assertEquals($expected, $type->generalize()->__toString());
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideGeneralize(): Generator
    {
        yield [
            // ['foo','bar']
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
                TypeFactory::arrayLiteral([
                    TypeFactory::stringLiteral('one'),
                    TypeFactory::stringLiteral('two'),
                ]),
            ]),
            'array<int,array<int,string>>',
        ];
        yield [
            // ['foo','bar']
            TypeFactory::arrayLiteral([
                TypeFactory::arrayShape([
                    'foo' => TypeFactory::intLiteral(12),
                    'bar' => TypeFactory::intLiteral(12),
                ])
            ]),
            'array<int,array{foo:int,bar:int}>',
        ];
    }
}
