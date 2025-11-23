<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\WorseReflection\Core\ClassHierarchyResolver;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\ReflectorBuilder;

final class ClassHierarchyResolverTest extends TestCase
{
    /**
     * @param list<string> $definitions
     * @param list<string> $expected
     */
    #[DataProvider('provideClassHierarchy')]
    public function testClassHierarchy(array $definitions, array $expected): void
    {
        $reflector = ReflectorBuilder::create()->addSource(implode("\n", $definitions))->build();
        $resolver = new ClassHierarchyResolver();
        $hierarchy  =$resolver->resolve($reflector->reflectClassLike('Foobar'));
        $names = array_map(fn (ReflectionClassLike $class) => $class->name()->__toString(), $hierarchy);

        self::assertEquals($expected, array_values($names));
    }

    public static function provideClassHierarchy(): Generator
    {
        yield [
            [
                '<?php class Foobar {}',
            ],
            [
                'Foobar'
            ],
        ];

        yield [
            [
                '<?php class Barfoo {}',
                '<?php class Foobar extends Barfoo {}',
            ],
            [
                'Barfoo',
                'Foobar',
            ],
        ];
        yield [
            [
                '<?php interface Barfoo {}',
                '<?php interface Foobar extends Barfoo {}',
            ],
            [
                'Barfoo',
                'Foobar',
            ],
        ];
        yield [
            [
                '<?php interface Barfoo {}',
                '<?php interface Foobar extends Barfoo {}',
            ],
            [
                'Barfoo',
                'Foobar',
            ],
        ];
    }
}
