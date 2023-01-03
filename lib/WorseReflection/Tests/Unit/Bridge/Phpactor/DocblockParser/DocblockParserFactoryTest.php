<?php

namespace Phpactor\WorseReflection\Tests\Unit\Bridge\Phpactor\DocblockParser;

use Generator;
use Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser\DocblockParserFactory;
use Phpactor\WorseReflection\Core\DocBlock\DocBlock;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClassLike;
use Phpactor\WorseReflection\Core\Reflection\ReflectionScope;
use Phpactor\WorseReflection\Core\Type;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\ArrayType;
use Phpactor\WorseReflection\Core\Type\BooleanType;
use Phpactor\WorseReflection\Core\Type\CallableType;
use Phpactor\WorseReflection\Core\Type\ConditionalType;
use Phpactor\WorseReflection\Core\Type\FloatType;
use Phpactor\WorseReflection\Core\Type\IntLiteralType;
use Phpactor\WorseReflection\Core\Type\IntMaxType;
use Phpactor\WorseReflection\Core\Type\IntType;
use Phpactor\WorseReflection\Core\Type\IntersectionType;
use Phpactor\WorseReflection\Core\Type\MissingType;
use Phpactor\WorseReflection\Core\Type\MixedType;
use Phpactor\WorseReflection\Core\Type\NullType;
use Phpactor\WorseReflection\Core\Type\ObjectType;
use Phpactor\WorseReflection\Core\Type\PseudoIterableType;
use Phpactor\WorseReflection\Core\Type\ResourceType;
use Phpactor\WorseReflection\Core\Type\StringType;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Core\Type\VoidType;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\ReflectorBuilder;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class DocblockParserFactoryTest extends IntegrationTestCase
{
    use ProphecyTrait;
    /**
     * @dataProvider provideResolveType
     * @param Type|string $expected
     */
    public function testResolveType(string $docblock, $expected): void
    {
        $docblock = $this->parseDocblock($docblock);

        if (is_string($expected)) {
            self::assertEquals($expected, $docblock->returnType()->__toString());
            return;
        }

        self::assertInstanceOf(get_class($expected), $docblock->returnType());
        self::assertEquals($expected->__toString(), $docblock->returnType()->__toString());
    }

    /**
     * @return Generator<mixed>
     */
    public function provideResolveType(): Generator
    {
        yield [
            '/** @return string */',
            new StringType()
        ];

        yield [
            '/** @return int */',
            new IntType()
        ];

        yield [
            '/** @return float */',
            new FloatType()
        ];

        yield [
            '/** @return mixed */',
            new MixedType()
        ];

        yield [
            '/** @return array */',
            new ArrayType(new MissingType())
        ];

        yield [
            '/** @return array|string */',
            new UnionType(new ArrayType(new MissingType()), new StringType())
        ];

        yield [
            '/** @return array&string */',
            new IntersectionType(new ArrayType(new MissingType()), new StringType())
        ];

        yield [
            '/** @return array<string> */',
            new ArrayType(new StringType())
        ];

        yield [
            '/** @return bool */',
            new BooleanType()
        ];

        yield [
            '/** @return null */',
            new NullType()
        ];

        yield [
            '/** @return callable */',
            new CallableType([], new MissingType())
        ];

        yield [
            '/** @return callable(): string */',
            new CallableType([], new StringType())
        ];
        yield [
            '/** @return callable(string,bool): string */',
            new CallableType([
                new StringType(),
                new BooleanType(),
            ], new StringType())
        ];
        yield [
            '/** @return iterable */',
            new PseudoIterableType(),
        ];
        yield [
            '/** @return object */',
            new ObjectType(),
        ];
        yield [
            '/** @return resource */',
            new ResourceType(),
        ];

        yield [
            '/** @return void */',
            new VoidType(),
        ];
        yield [
            '/** @return array<int, string> */',
            new ArrayType(new IntType(), new StringType())
        ];
        yield [
            '/** @return array<int, array<string,bool>> */',
            new ArrayType(new IntType(), new ArrayType(new StringType(), new BooleanType()))
        ];

        yield 'nullable' => [
            '/** @return ?string */',
            '?string',
        ];

        yield [
            '/** @return T */',
            'T',
        ];

        yield [
            '/** @return \IteratorAggregate<Foobar> */',
            'IteratorAggregate<Foobar>',
        ];

        yield [
            '/** @return array{} */',
            'array{}',
        ];

        yield 'arrayshape with keys' => [
            '/** @return array{foo:int,bar:string} */',
            'array{foo:int,bar:string}',
        ];

        yield 'parenthesized' => [
            '/** @return null|(callable(int):string)|string|int */',
            'null|(callable(int): string)|string|int',
        ];

        yield 'multiline array shape' => [
            <<<'EOT'
                /**
                 * @return array{
                 *   foo:int,
                 *   bar:string
                 * }
                 */
                EOT
                ,
            'array{foo:int,bar:string}',
        ];

        yield 'literals' => [
            '/** @return null|"foo"|123|123.3 */',
            'null|"foo"|123|123.3',
        ];

        yield 'list' => [
            '/** @return list */',
            TypeFactory::list(),
        ];

        yield 'list with type' => [
            '/** @return list<string> */',
            TypeFactory::list(TypeFactory::string()),
        ];

        yield 'never' => [
            '/** @return never */',
            TypeFactory::never(),
        ];

        yield 'false' => [
            '/** @return false */',
            TypeFactory::false(),
        ];
        yield 'union false' => [
            '/** @return false|int */',
            TypeFactory::union(TypeFactory::false(), TypeFactory::int())
        ];

        yield 'psalm prefix' => [
            '/** @psalm-return int */',
            TypeFactory::int(),
        ];

        yield 'conditional type' => [
            '/** @return ($foo is true ? string : int) */',
            TypeFactory::parenthesized(
                new ConditionalType(
                    '$foo',
                    TypeFactory::boolLiteral(true),
                    TypeFactory::string(),
                    TypeFactory::int()
                )
            )
        ];

        yield 'class string generic' => [
            '/** @return class-string<T> */',
            TypeFactory::classString('T'),
        ];

        yield 'int range max' => [
            '/** @return int<12, max> */',
            TypeFactory::intRange(
                new IntLiteralType(12),
                new IntMaxType(PHP_INT_MAX)
            )
        ];

        yield 'int range' => [
            '/** @return int<12, 23> */',
            TypeFactory::intRange(
                new IntLiteralType(12),
                new IntLiteralType(23),
            )
        ];

        yield 'int positive' => [
            '/** @return positive-int */',
            TypeFactory::intPositive()
        ];

        yield 'int negative' => [
            '/** @return negative-int */',
            TypeFactory::intNegative()
        ];
    }

    public function testClassConstant(): void
    {
        $source = <<<'EOT'
                        <?php
                        namespace Bar;

                        class Foo {
                            const BAR = "baz";
                        }
            EOT;
        $reflector = ReflectorBuilder::create()->addSource($source)->build();
        $class = $reflector->reflectClassesIn(
            $source
        )->first();
        $docblock = $this->parseDocblockWithClass($reflector, $class, '/** @return self::BAR */');
        self::assertEquals('self::BAR', $docblock->returnType()->__toString());
    }

    public function testClassConstantGlob(): void
    {
        $source = <<<'EOT'
                        <?php
                        class Foo {
                            const BAZ = "baz";
                            const BAR = "bar";
                            const ZED = "zed";
                            const SED = "sed";
                        }
            EOT;
        $reflector = ReflectorBuilder::create()->addSource($source)->build();
        $class = $reflector->reflectClassesIn($source)->first();
        $docblock = $this->parseDocblockWithClass($reflector, $class, '/** @return Foo::BA* */');
        self::assertEquals('Foo::BA*', $docblock->returnType()->__toString());
    }

    public function testClassConstantGlobInArrayShape(): void
    {
        $source = <<<'EOT'
                        <?php
                        class Foo {
                            const BAZ = "baz";
                            const BAR = "bar";
                            const ZED = "zed";
                            const SED = "sed";
                        }
            EOT;
        $reflector = ReflectorBuilder::create()->addSource($source)->build();
        $class = $reflector->reflectClassesIn($source)->first();
        $docblock = $this->parseDocblockWithClass($reflector, $class, '/** @return array{string,Foo::*} */');
        self::assertEquals('array{string,Foo::*}', $docblock->returnType()->__toString());
    }

    public function testMethods(): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $docblock = $this->parseDocblockWithReflector($reflector, '/** @method Barfoo foobar() */');
        $methods = $docblock->methods($reflector->reflectClass('Bar\Foobar'));

        self::assertEquals('foobar', $methods->first()->name());
        self::assertEquals('Barfoo', $methods->first()->type());
    }

    public function testStaticMethods(): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $docblock = $this->parseDocblockWithReflector($reflector, '/** @method static Barfoo foobar() */');
        $methods = $docblock->methods($reflector->reflectClass('Bar\Foobar'));

        self::assertEquals('foobar', $methods->first()->name());
        self::assertEquals('Barfoo', $methods->first()->type());
        self::assertTrue($methods->first()->isStatic());
    }

    public function testMethodsWithParams(): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $docblock = $this->parseDocblockWithReflector($reflector, '/** @method Barfoo foobar(string $foobar, int $barfoo) */');
        $methods = $docblock->methods($reflector->reflectClass('Bar\Foobar'));

        self::assertEquals('foobar', $methods->first()->name());
        self::assertEquals('Barfoo', $methods->first()->type());
        self::assertEquals('foobar', $methods->first()->parameters()->first()->name());
        self::assertEquals('string', $methods->first()->parameters()->first()->type());
        self::assertEquals('barfoo', $methods->first()->parameters()->get('barfoo')->name());
        self::assertEquals('int', $methods->first()->parameters()->get('barfoo')->type());
    }

    public function testProperties(): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $docblock = $this->parseDocblockWithReflector($reflector, '/** @property Barfoo $foobar */');
        $methods = $docblock->properties($reflector->reflectClass('Bar\Foobar'));

        self::assertEquals('foobar', $methods->first()->name());
        self::assertEquals('Barfoo', $methods->first()->type()->__toString());
    }

    public function testVars(): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $docblock = $this->parseDocblockWithReflector($reflector, '/** @var Barfoo */');
        $vars = $docblock->vars();
        self::assertEquals('Barfoo', $vars->type());
    }

    public function testVarsWithName(): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $docblock = $this->parseDocblockWithReflector($reflector, '/** @var Barfoo $foo */');
        $vars = iterator_to_array($docblock->vars());
        self::assertCount(1, $vars);
        self::assertEquals('Barfoo', $vars[0]->type());
        self::assertEquals('foo', $vars[0]->name());
    }

    public function testParameterType(): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $docblock = $this->parseDocblockWithReflector($reflector, '/** @param Barfoo $foobar */');
        $type = $docblock->parameterType('foobar');
        self::assertEquals('Barfoo', $type->__toString());
    }

    public function testPropertyType(): void
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        $docblock = $this->parseDocblockWithReflector($reflector, '/** @property Barfoo $foobar */');
        $type = $docblock->propertyType('foobar');
        self::assertEquals('Barfoo', $type->__toString());
    }

    private function parseDocblock(string $docblock): DocBlock
    {
        $reflector = $this->createReflector('<?php namespace Bar; class Foobar{}');
        return $this->parseDocblockWithReflector($reflector, $docblock);
    }

    private function parseDocblockWithReflector(Reflector $reflector, string $docblock): DocBlock
    {
        $scope = $this->prophesize(ReflectionScope::class);
        $scope->resolveFullyQualifiedName(Argument::any())->will(fn ($args) => $args[0]);

        return (new DocblockParserFactory($reflector))->create($docblock, $scope->reveal());
    }

    private function parseDocblockWithClass(Reflector $reflector, ReflectionClassLike $classLike, string $docblock): DocBlock
    {
        return (new DocblockParserFactory($reflector))->create($docblock, $classLike->scope());
    }
}
