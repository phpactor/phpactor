<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Type;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Trinary;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\ReflectorBuilder;

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

    /**
     * @dataProvider provideCombine
     * @param Type[] $types
     */
    public function testCombine(array $types, array $narrows, string $expected): void
    {
        self::assertEquals(
            $expected,
            TypeFactory::union(...$types)->narrowTo(TypeFactory::union(...$narrows))->__toString()
        );
    }

    /**
     * @return Generator<mixed>
     */
    public function provideCombine(): Generator
    {
        yield [
            [
                TypeFactory::string(),
            ],
            [
            ],
            'string'
        ];

        yield 'narrow to mixed' => [
            [
                TypeFactory::string(),
            ],
            [
                TypeFactory::mixed(),
            ],
            'string|mixed'
        ];

        yield 'mixed narrows to int' => [
            [
                TypeFactory::mixed(),
            ],
            [
                TypeFactory::int(),
            ],
            'int'
        ];

        yield 'mixed and string narrows to int' => [
            [
                TypeFactory::mixed(),
                TypeFactory::string(),
            ],
            [
                TypeFactory::int(),
            ],
            'string|int'
        ];

        yield 'empty narrow with classes' => [
            $this->classTypes(
                '<?php abstract class Foobar {} class Barfoo extends Foobar {}',
                'Foobar',
                'Barfoo',
            ),
            [
            ],
            'Foobar|Barfoo',
        ];

        yield 'narrow abstract class to concerete' => [
            $this->classTypes(
                '<?php abstract class Foobar {} class Barfoo extends Foobar {}',
                'Foobar',
                'Barfoo',
            ),
            [
                TypeFactory::class('Barfoo'),
            ],
            'Barfoo',
        ];

        yield 'narrow abstract class to concerete with other types' => [
            array_merge(
                $this->classTypes(
                    '<?php abstract class Foobar {} class Barfoo extends Foobar {}',
                    'Foobar',
                    'Barfoo',
                ),
                [
                    TypeFactory::string(),
                ],
            ),
            [
                TypeFactory::class('Barfoo'),
            ],
            'Barfoo|string',
        ];

        yield 'narrow abstract with interface' => [
            $this->classTypes(
                '<?php interface Bar {} abstract class Foobar implements Bar {} class Barfoo extends Foobar {}',
                'Foobar',
                'Barfoo',
            ),
            [
                TypeFactory::class('Bar'),
            ],
            'Foobar|Barfoo|Bar',
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

    /**
     * @dataProvider provideAccepts
     */
    public function testAccepts(UnionType $union, Type $type, Trinary $accepts): void
    {
        self::assertEquals($accepts, $union->accepts($type));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideAccepts(): Generator
    {
        yield [
            TypeFactory::union(TypeFactory::int(), TypeFactory::string()),
            TypeFactory::int(),
            Trinary::true(),
        ];
        yield [
            TypeFactory::union(TypeFactory::int(), TypeFactory::string()),
            TypeFactory::class('Foobar'),
            Trinary::false(),
        ];
        yield 'int literal maybe accepts int' => [
            TypeFactory::union(TypeFactory::intLiteral(12), TypeFactory::string()),
            TypeFactory::int(),
            Trinary::maybe(),
        ];
        yield 'string literal maybe string' => [
            TypeFactory::union(TypeFactory::stringLiteral('foo')),
            TypeFactory::string(),
            Trinary::maybe(),
        ];
        yield 'bool literal maybe bool' => [
            TypeFactory::union(TypeFactory::boolLiteral(true)),
            TypeFactory::bool(),
            Trinary::maybe(),
        ];
        yield 'float literal maybe float' => [
            TypeFactory::union(TypeFactory::floatLiteral(12.2)),
            TypeFactory::float(),
            Trinary::maybe(),
        ];
        yield 'boolean true is not empty' => [
            TypeFactory::union(TypeFactory::unionEmpty()),
            TypeFactory::boolLiteral(true),
            Trinary::false(),
        ];
    }

    public function testToPhpString(): void
    {
        self::assertEquals(
            'Foobar|array',
            TypeFactory::union(
                TypeFactory::class('Foobar'),
                TypeFactory::array('string')
            )->toPhpString()
        );
    }

    /**
     * @return Type[]
     */
    private function classTypes(string $string, string ...$classNames): array
    {
        $reflector = ReflectorBuilder::create()->addSource($string)->build();
        return array_values(array_map(function (string $className) use ($reflector) {
            return TypeFactory::reflectedClass($reflector, $className);
        }, $classNames));
    }
}
