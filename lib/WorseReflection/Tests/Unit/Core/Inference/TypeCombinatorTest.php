<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core\Inference;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Inference\TypeCombinator;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\ReflectorBuilder;

class TypeCombinatorTest extends TestCase
{
    /**
     * @dataProvider provideNarrow
     * @param Type[] $types
     * @param Type[] $narrows
     */
    public function testNarrow(array $types, array $narrows, string $expected): void
    {
        self::assertEquals(
            $expected,
            TypeCombinator::narrowTo(TypeFactory::union(...$types), TypeFactory::union(...$narrows))->__toString()
        );
    }

    /**
     * @return Generator<mixed>
     */
    public function provideNarrow(): Generator
    {
        yield [
            [
                TypeFactory::string(),
            ],
            [
            ],
            'string'
        ];

        yield 'cannot narrow from smaller to wider (e.g. string to mixed)' => [
            [
                TypeFactory::string(),
            ],
            [
                TypeFactory::mixed(),
            ],
            '<missing>'
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
            'int'
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

        $classTypes = $this->classTypes(
            '<?php abstract class Foobar {} class Barfoo extends Foobar {}',
            'Foobar',
            'Barfoo',
        );

        yield 'narrow abstract class to concerete' => [
            [
                $classTypes[0],
                $classTypes[1],
            ],
            [
                $classTypes[1],
            ],
            'Barfoo',
        ];

        yield 'narrow abstract class to concerete with other types' => [
            array_merge(
                [
                    $classTypes[0],
                    $classTypes[1],
                ],
                [
                    TypeFactory::string(),
                ],
            ),
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
            [
                $classTypes[0],
                $classTypes[1],
            ],
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
            [
                $classTypes[0],
                $classTypes[1],
                $classTypes[2],
            ],
            [
                $classTypes[1],
            ],
            'Barfoo',
        ];

        yield 'strips unknown types' => [
            [
                TypeFactory::unknown(),
                TypeFactory::string(),
            ],
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
            [
                $classTypes[0],
                $classTypes[1],
            ],
            [
                $classTypes[1],
            ],
            'Bar',
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
