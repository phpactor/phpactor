<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Type;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\UnionType;

class UnionTypeTest extends TestCase
{
    /**
     * @dataProvider provideRemove
     * @param Type[] $types
     */
    public function testRemove(array $types, Type $remove, string $expected): void
    {
        self::assertEquals($expected, TypeFactory::union(...$types)->remove($remove)->__toString());
    }

    /**
     * @return Generator<mixed>
     */
    public function provideRemove(): Generator
    {
        yield [
            [
            ],
            new MissingType(),
            '<missing>'
        ];

        yield 'do not remove existing type' => [
            [
                TypeFactory::string(),
            ],
            new MissingType(),
            'string'
        ];

        yield 'remove union' => [
            [
                TypeFactory::string(),
                TypeFactory::int(),
                TypeFactory::class('Foo'),
                TypeFactory::class('Bar'),
            ],
            TypeFactory::union(
                TypeFactory::string(),
                TypeFactory::class('Bar'),
            ),
            'int|Foo',
        ];
    }

    /**
     * @dataProvider provideFilter
     * @param Type[] $types
     */
    public function testFilter(array $types, string $expected): void
    {
        self::assertEquals($expected, TypeFactory::union(...$types)->filter()->__toString());
    }

    /**
     * @return Generator<mixed>
     */
    public function provideFilter(): Generator
    {
        yield [[], ''];
        yield [[TypeFactory::undefined()], ''];
        yield [[TypeFactory::string()], 'string'];

        yield [
            [
                TypeFactory::string(),
                TypeFactory::string(),
                TypeFactory::string(),
                TypeFactory::string(),
            ],
            'string'
        ];

        yield [
            [
                TypeFactory::string(),
                TypeFactory::int(),
                TypeFactory::string(),
                TypeFactory::string(),
            ],
            'string|int'
        ];
    }
}
