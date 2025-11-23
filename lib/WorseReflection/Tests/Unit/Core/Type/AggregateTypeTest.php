<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\FloatType;
use Phpactor\WorseReflection\Core\Type\MissingType;

class AggregateTypeTest extends TestCase
{
    /**
     * @param Type[] $types
     */
    #[DataProvider('provideRemove')]
    public function testRemove(array $types, Type $remove, string $expected): void
    {
        self::assertEquals($expected, TypeFactory::union(...$types)->remove($remove)->__toString());
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideRemove(): Generator
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
     * @param Type[] $types
     */
    #[DataProvider('provideClean')]
    public function testClean(array $types, string $expected): void
    {
        self::assertEquals($expected, TypeFactory::union(...$types)->clean()->__toString());
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideClean(): Generator
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

    public function testFilter(): void
    {
        $types = TypeFactory::union(
            TypeFactory::int(),
            TypeFactory::float(),
        )->filter(fn (Type $type) => $type instanceof FloatType);

        self::assertEquals(TypeFactory::union(TypeFactory::float()), $types);
    }

    /**
     * @param Type[] $types
     */
    #[DataProvider('provideReduce')]
    public function testReduce(array $types, string $expected): void
    {
        self::assertEquals($expected, TypeFactory::union(...$types)->reduce()->__toString());
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideReduce(): Generator
    {
        yield [[], '<missing>'];
        yield [[TypeFactory::undefined()], '<missing>'];
        yield [[TypeFactory::string(), ], 'string'];

        yield 'strips parenthesis' => [
            [
                TypeFactory::parenthesized(TypeFactory::string()),
            ],
            'string'
        ];
    }

    public function testDeduplicatesTypesOnConstruct(): void
    {
        self::assertEquals('One|Two', TypeFactory::union(
            TypeFactory::class('One'),
            TypeFactory::class('Two'),
            TypeFactory::class('One'),
            TypeFactory::class('Two'),
        )->__toString());
    }

    public function testDedupesNullOnConstruct(): void
    {
        self::assertEquals('null|One|Two', TypeFactory::union(
            TypeFactory::nullable(TypeFactory::class('One')),
            TypeFactory::class('Two'),
            TypeFactory::class('One'),
            TypeFactory::null(),
            TypeFactory::null(),
        )->__toString());
    }

    public function testRemovesPointlessParenthesisForIntersection(): void
    {
        self::assertEquals('null|One|Two', TypeFactory::union(
            TypeFactory::null(),
            TypeFactory::intersection(TypeFactory::class('One')),
            TypeFactory::class('Two')
        )->__toString());
    }

    public function testAddsParenthesisForIntersection(): void
    {
        self::assertEquals('null|(One&string)|Two', TypeFactory::union(
            TypeFactory::null(),
            TypeFactory::intersection(TypeFactory::class('One'), TypeFactory::string()),
            TypeFactory::class('Two')
        )->__toString());
    }

    public function testMergeUnions(): void
    {
        self::assertEquals(
            TypeFactory::union(
                TypeFactory::class('Two'),
                TypeFactory::class('One'),
                TypeFactory::string(),
                TypeFactory::int()
            ),
            TypeFactory::union(
                TypeFactory::class('Two'),
                TypeFactory::class('One'),
                TypeFactory::union(
                    TypeFactory::string(),
                    TypeFactory::int()
                )
            )
        );
    }

    public function testToPhpString(): void
    {
        self::assertEquals(
            'Foobar|array',
            TypeFactory::union(
                TypeFactory::class('Foobar'),
                TypeFactory::array(TypeFactory::string())
            )->toPhpString()
        );
    }
}
