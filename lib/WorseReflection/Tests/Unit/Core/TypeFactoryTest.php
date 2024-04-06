<?php

namespace Phpactor\WorseReflection\Tests\Unit\Core;

use Generator;
use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\FalseType;
use Phpactor\WorseReflection\Core\Type\NeverType;
use Phpactor\WorseReflection\ReflectorBuilder;
use stdClass;

class TypeFactoryTest extends TestCase
{
    public function testNever(): void
    {
        self::assertEquals(new NeverType(), TypeFactory::fromString('never'));
    }

    public function testFalse(): void
    {
        self::assertEquals(new FalseType(), TypeFactory::fromString('false'));
    }

    /**
     * @testdox It should __toString the given type.
     * @dataProvider provideToString
     */
    public function testToString(Type $type, $toString, $phpType): void
    {
        $this->assertEquals($toString, (string) $type, '__toString()');
        $this->assertEquals($phpType, $type->toPhpString(), 'phptype');
    }

    public function provideToString(): Generator
    {
        $reflector = ReflectorBuilder::create()->build();
        yield [
            TypeFactory::fromString('string'),
            'string',
            'string',
        ];

        yield [
            TypeFactory::fromString('class-string'),
            'class-string',
            'string',
        ];

        yield [
            TypeFactory::fromString('float'),
            'float',
            'float',
        ];

        yield [
            TypeFactory::fromString('int'),
            'int',
            'int',
        ];

        yield [
            TypeFactory::fromString('bool'),
            'bool',
            'bool',
        ];

        yield [
            TypeFactory::fromString('array'),
            'array',
            'array',
        ];

        yield [
            TypeFactory::fromString('void'),
            'void',
            'void',
        ];

        yield [
            TypeFactory::fromString('Foobar'),
            'Foobar',
            'Foobar'
        ];

        yield [
            TypeFactory::fromString('mixed'),
            'mixed',
            'mixed'
        ];

        yield 'Collection' => [
            TypeFactory::collection($reflector, 'Foobar', TypeFactory::string()),
            'Foobar<string>',
            'Foobar',
        ];

        yield 'Typed array' => [
            TypeFactory::array(TypeFactory::string()),
            'string[]',
            'array',
        ];

        yield 'Nullable string' => [
            TypeFactory::fromString('?string'),
            '?string',
            '?string',
        ];

        yield 'Nullable class' => [
            TypeFactory::fromString('?Foobar'),
            '?Foobar',
            '?Foobar',
        ];

        yield 'Nullable iterable class' => [
            TypeFactory::nullable(TypeFactory::collection($reflector, 'Foo', 'Bar')),
            '?Foo<Bar>',
            '?Foo',
        ];

        yield 'callable' => [
            TypeFactory::fromString('callable'),
            'callable()',
            'callable'
        ];

        yield 'iterable' => [
            TypeFactory::fromString('iterable'),
            'iterable',
            'iterable'
        ];

        yield 'resource' => [
            TypeFactory::fromString('resource'),
            'resource',
            'resource'
        ];

        yield 'class-string' => [
            TypeFactory::fromString('class-string'),
            'class-string',
            'string',
        ];

        yield 'list' => [
            TypeFactory::fromString('class-string'),
            'class-string',
            'string',
        ];

        yield 'false' => [
            TypeFactory::fromString('false'),
            'false',
            'false',
        ];
    }

    /**
     * @dataProvider provideValues
     */
    public function testItCanBeCreatedFromAValue($value, Type $expectedType): void
    {
        $type = TypeFactory::fromValue($value);
        $this->assertEquals($expectedType, $type);
    }

    public function provideValues(): Generator
    {
        yield [
            'string',
            TypeFactory::stringLiteral('string'),
        ];

        yield [
            11,
            TypeFactory::intLiteral(11),
        ];

        yield [
            11.2,
            TypeFactory::floatLiteral(11.2),
        ];

        yield [
            [],
            TypeFactory::array(),
        ];

        yield [
            true,
            TypeFactory::boolLiteral(true),
        ];

        yield [
            false,
            TypeFactory::boolLiteral(false),
        ];

        yield [
            null,
            TypeFactory::null(),
        ];

        yield [
            new stdClass(),
            TypeFactory::class(ClassName::fromString('stdClass')),
        ];

        yield 'resource' => [
            \fopen(__FILE__, 'r'),
            TypeFactory::resource(),
        ];

        yield 'callable' => [
            function (): void {
            },
            TypeFactory::callable(),
        ];
    }
}
