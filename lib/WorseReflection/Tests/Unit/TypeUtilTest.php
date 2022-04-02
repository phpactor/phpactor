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
            (string)TypeUtil::toLocalType($class->scope(), $type)
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
            TypeFactory::array('Foo\Baz'),
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
}
