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
