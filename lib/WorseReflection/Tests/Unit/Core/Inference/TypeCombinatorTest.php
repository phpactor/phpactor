<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\TypeCombinator;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\ReflectorBuilder;

class TypeCombinatorTest extends TestCase
{
    /**
     * @param Type[] $narrows
     */
    #[DataProvider('provideNarrow')]
    public function testNarrow(Type $type, array $narrows, string $expected): void
    {
        self::assertEquals(
            $expected,
            TypeCombinator::narrowTo($type, TypeFactory::union(...$narrows))->__toString()
        );
    }

    /**
     * @return Generator<mixed>
     */
    public function provideNarrow(): Generator
    {
        yield 'cannot narrow from smaller to wider (e.g. string to mixed)' => [
            TypeFactory::union(
                TypeFactory::string(),
            ),
            [
                TypeFactory::mixed(),
            ],
            '<missing>'
        ];

        yield 'mixed narrows to int' => [
            TypeFactory::union(
                TypeFactory::mixed(),
            ),
            [
                TypeFactory::int(),
            ],
            'int'
        ];

        yield 'mixed and string narrows to int' => [
            TypeFactory::union(
                TypeFactory::mixed(),
                TypeFactory::string(),
            ),
            [
                TypeFactory::int(),
            ],
            'int'
        ];

        $classTypes = $this->classTypes(
            '<?php abstract class Foobar {} class Barfoo extends Foobar {}',
            'Foobar',
            'Barfoo',
        );

        yield 'narrow abstract class to concerete' => [
            TypeFactory::union(
                $classTypes[0],
                $classTypes[1],
            ),
            [
                $classTypes[1],
            ],
            'Barfoo',
        ];

        yield 'narrow abstract class to concerete with other types' => [
            TypeFactory::union(...array_merge(
                [
                    $classTypes[0],
                    $classTypes[1],
                ],
                [
                    TypeFactory::string(),
                ],
            )),
            [
                $classTypes[1],
            ],
            'Barfoo',
        ];

        $classTypes = $this->classTypes(
            '<?php interface Bar {} class Foobar {} class Barfoo {}',
            'Foobar',
            'Barfoo',
            'Bar',
        );

        yield 'intersection' => [
            TypeFactory::union(
                $classTypes[0],
                $classTypes[1],
            ),
            [
                $classTypes[2],
            ],
            '(Foobar&Bar)|(Barfoo&Bar)',
        ];
        $classTypes = $this->classTypes(
            '<?php class Foobar {} class Barfoo {} class Bazboo {}',
            'Foobar',
            'Barfoo',
            'Bazboo',
        );

        yield 'narrow union type' => [
            TypeFactory::union(
                $classTypes[0],
                $classTypes[1],
                $classTypes[2],
            ),
            [
                $classTypes[1],
            ],
            'Barfoo',
        ];

        yield 'strips unknown types' => [
            TypeFactory::union(
                TypeFactory::unknown(),
                TypeFactory::string(),
            ),
            [
                TypeFactory::string(),
            ],
            'string',
        ];

        $classTypes = $this->classTypes(
            '<?php class Foo {}',
            'Foo',
            'Bar',
        );

        yield 'narrow union to unknown type ' => [
            TypeFactory::union(
                $classTypes[0],
                $classTypes[1],
            ),
            [
                $classTypes[1],
            ],
            'Bar',
        ];

        yield 'narrow intersection to unknown type ' => [
            TypeFactory::intersection(
                $classTypes[0],
                TypeFactory::class('Car'),
            ),
            [
                $classTypes[1],
            ],
            'Foo&Car&Bar',
        ];

        yield 'narrow parenthesized intersection to unknown type ' => [
            TypeFactory::parenthesized(
                TypeFactory::intersection(
                    $classTypes[0],
                    TypeFactory::class('Car'),
                )
            ),
            [
                $classTypes[1],
            ],
            'Foo&Car&Bar',
        ];

        yield 'narrow parenthesized intersection to intersection type ' => [
            TypeFactory::parenthesized(
                TypeFactory::intersection(
                    $classTypes[0],
                    TypeFactory::class('Car'),
                )
            ),
            [
                TypeFactory::parenthesized(
                    TypeFactory::intersection(
                        $classTypes[1],
                        TypeFactory::class('Dar'),
                    )
                ),
            ],
            'Foo&Car&Bar&Dar',
        ];
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
