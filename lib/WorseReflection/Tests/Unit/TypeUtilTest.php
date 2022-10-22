<?php

namespace Phpactor\WorseReflection\Tests\Unit;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\GenericClassType;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\WorseReflection\TypeUtil;

class TypeUtilTest extends TestCase
{
    /**
     * @dataProvider provideToLocalType
     */
    public function testToLocalType(string $source, Type $type, string $expected): void
    {
        $reflector = ReflectorBuilder::create()->addSource($source)->build();
        $class = $reflector->reflectClassLike('Foo');
        self::assertEquals(
            $expected,
            (string)$type->toLocalType($class->scope())
        );
    }

    public function provideToLocalType(): Generator
    {
        $reflector = ReflectorBuilder::create()->build();
        yield [
            '<?php class Foo{}',
            TypeFactory::string(),
            'string',
        ];

        yield [
            '<?php class Foo{}',
            TypeFactory::class('Foo\Baz'),
            'Baz',
        ];

        yield [
            '<?php use Foo\Baz as Boo; class Foo{}',
            TypeFactory::class('Foo\Baz'),
            'Boo',
        ];

        yield [
            '<?php use Foo\Baz as Boo; class Foo{}',
            TypeFactory::array(TypeFactory::class('Foo\Baz')),
            'Boo[]',
        ];

        yield [
            '<?php use Foo\Baz as Boo; class Foo{}',
            new GenericClassType($reflector, ClassName::fromString('Foo'), [
                TypeFactory::fromString('string'),
                TypeFactory::fromString('Foo\Baz'),
            ]),
            'Foo<string,Boo>',
        ];
    }

    /**
     * @dataProvider provideShort
     */
    public function testShort(Type $type, string $expected): void
    {
        self::assertEquals(
            $expected,
            $type->short(),
        );
    }

    public function provideShort(): Generator
    {
        yield 'scalar' => [
            TypeFactory::string(),
            'string',
        ];

        yield 'Root class' => [
            TypeFactory::class('Foo'),
            'Foo',
        ];
        yield 'Namespaced class' => [
            TypeFactory::class('\Foo\Bar'),
            'Bar',
        ];
        yield 'Union' => [
            TypeFactory::union(
                TypeFactory::class('\Foo\Bar'),
            ),
            'Bar',
        ];
        yield 'Union with two elements' => [
            TypeFactory::union(
                TypeFactory::class('\Foo\Bar'),
                TypeFactory::class('\Foo\Baz'),
            ),
            'Bar|Baz',
        ];
    }

    /**
     * @dataProvider provideShortenClassTypes
     */
    public function testShortenClassTypes(Type $type, string $expected): void
    {
        self::assertEquals(
            $expected,
            TypeUtil::shortenClassTypes($type)->__toString()
        );
    }

    public function provideShortenClassTypes(): Generator
    {
        yield 'scalar' => [
            TypeFactory::string(),
            'string',
        ];

        yield 'Root class' => [
            TypeFactory::class('Foo'),
            'Foo',
        ];
        yield 'Namespaced class' => [
            TypeFactory::class('\Foo\Bar'),
            'Bar',
        ];
        yield 'Union' => [
            TypeFactory::union(
                TypeFactory::class('\Foo\Bar'),
            ),
            'Bar',
        ];
        yield 'Union with two elements' => [
            TypeFactory::union(
                TypeFactory::class('\Foo\Bar'),
                TypeFactory::class('\Foo\Baz'),
            ),
            'Bar|Baz',
        ];
        yield 'Static' => [
            TypeFactory::static(
                TypeFactory::class('\Foo\Bar'),
            ),
            'static(Bar)',
        ];
        yield 'This' => [
            TypeFactory::this(
                TypeFactory::class('\Foo\Bar'),
            ),
            '$this(Bar)',
        ];
        yield 'Nullable' => [
            TypeFactory::nullable(
                TypeFactory::class('\Foo\Bar'),
            ),
            '?Bar',
        ];
    }
}
