<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Generator;
use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionMethodCollection;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Tests\Assert\TrinaryAssert;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Reflection\ReflectionMethod;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Psr\Log\LoggerInterface;
use Closure;

class ReflectionMethodTest extends IntegrationTestCase
{
    use TrinaryAssert;

    /**
     * @dataProvider provideReflectionMethod
     * @dataProvider provideGenerics
     * @dataProvider provideDeprecations
     */
    public function testReflectMethod(string $source, string $class, Closure $assertion): void
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class->methods(), $this->logger());
    }

    public function provideReflectionMethod(): Generator
    {
        yield 'It reflects a method' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function method();
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertEquals('method', $methods->get('method')->name());
                $this->assertInstanceOf(ReflectionMethod::class, $methods->get('method'));
            },
        ];
        yield 'Private visibility' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    private function method();
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertEquals(Visibility::private(), $methods->get('method')->visibility());
            },
        ];
        yield 'Protected visibility' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    protected function method()
                    {
                    }
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertEquals(Visibility::protected(), $methods->get('method')->visibility());
            },
        ];
        yield 'Public visibility' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function method();
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertEquals(Visibility::public(), $methods->get('method')->visibility());
            },
        ];
        yield 'Union type' => [
            <<<'EOT'
                <?php

                class Foobar { function method1(): string|int {} }
                EOT
        ,
            'Foobar',
            function (ReflectionMethodCollection $methods): void {
                $this->assertEquals(new UnionType(
                    TypeFactory::string(),
                    TypeFactory::int(),
                ), $methods->get('method1')->inferredType());
            },
        ];
        yield 'Return type' => [
            <<<'EOT'
                <?php

                namespace Test;

                use Acme\Post;

                class Foobar
                {
                    function method1(): int {}
                    function method2(): string {}
                    function method3(): float {}
                    function method4(): array {}
                    function method5(): Barfoo {}
                    function method6(): Post {}
                    function method7(): self {}
                    function method8(): iterable {}
                    function method9(): callable {}
                    function method10(): resource {}
                }
                EOT
        ,
            'Test\Foobar',
            function ($methods): void {
                $this->assertEquals(TypeFactory::int(), $methods->get('method1')->returnType());
                $this->assertEquals(TypeFactory::string(), $methods->get('method2')->returnType());
                $this->assertEquals(TypeFactory::float(), $methods->get('method3')->returnType());
                $this->assertEquals(TypeFactory::array(), $methods->get('method4')->returnType());
                $this->assertEquals(ClassName::fromString('Test\Barfoo'), $methods->get('method5')->returnType()->name);
                $this->assertEquals(ClassName::fromString('Acme\Post'), $methods->get('method6')->returnType()->name);
                $this->assertEquals('self(Test\Foobar)', $methods->get('method7')->returnType()->__toString());
                $this->assertEquals(TypeFactory::iterable(), $methods->get('method8')->returnType());
                $this->assertEquals(TypeFactory::callable(), $methods->get('method9')->returnType());
                $this->assertEquals(TypeFactory::resource(), $methods->get('method10')->returnType());
            },
        ];
        yield 'Nullable return type' => [
            <<<'EOT'
                <?php

                use Acme\Post;

                class Foobar
                {
                    function method1(): ?int {}
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertEquals(
                    TypeFactory::fromString('?int'),
                    $methods->get('method1')->returnType()
                );
            },
        ];
        yield 'Inherited methods' => [
            <<<'EOT'
                <?php

                class ParentParentClass extends NonExisting
                {
                    public function method5() {}
                }

                class ParentClass extends ParentParentClass
                {
                    private function method1() {}
                    protected function method2() {}
                    public function method3() {}
                    public function method4() {}
                }

                class Foobar extends ParentClass
                {
                    public function method4() {} // overrides from previous
                }
                EOT
        ,
            'Foobar',
            function (ReflectionMethodCollection $methods): void {
                $this->assertEquals(
                    ['method5', 'method2', 'method3', 'method4'],
                    $methods->keys()
                );
                self::assertEquals('Foobar', $methods->get('method5')->class()->name()->head()->__toString());
            },
        ];

        yield 'Return type from docblock' => [
            <<<'EOT'
                <?php

                use Acme\Post;

                class Foobar
                {
                    /**
                     * @return Post
                     */
                    function method1() {}
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertEquals(
                    'Acme\Post',
                    $methods->get('method1')->inferredType()->__toString(),
                );
            },
        ];

        yield 'Return type from array docblock' => [
            <<<'EOT'
                <?php

                use Acme\Post;

                class Foobar
                {
                    /**
                     * @return Post[]
                     */
                    function method1(): array {}
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertEquals(
                    'Acme\Post[]',
                    $methods->get('method1')->inferredType()->__toString()
                );
            },
        ];
        yield 'Return type from docblock this and static' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @return $this
                     */
                    function method1() {}

                    /**
                     * @return static
                     */
                    function method2() {}
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertEquals('$this(Foobar)', $methods->get('method1')->inferredType()->__toString(), '$this(Foobar)');
                $this->assertEquals('static(Foobar)', $methods->get('method2')->inferredType()->__toString(), 'static(Foobar)');
            },
        ];
        yield 'Return type from docblock this and static from a trait' => [
            <<<'EOT'
                <?php

                trait FooTrait
                {
                    /**
                     * @return $this
                     */
                    function method1() {}

                    /**
                     * @return static
                     */
                    function method2() {}
                }

                class Foobar
                {
                    use FooTrait;
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertEquals('$this(Foobar)', $methods->get('method1')->inferredType()->__toString());
                $this->assertEquals('static(Foobar)', $methods->get('method2')->inferredType()->__toString());
            },
        ];
        yield 'Return type from class @method annotation' => [
            <<<'EOT'
                <?php

                use Acme\Post;

                /**
                 * @method Post method1()
                 */
                class Foobar
                {
                    function method1() {}
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                self::assertTrinaryTrue(
                    TypeFactory::class(
                        ClassName::fromString('Acme\Post')
                    )->is(
                        $methods->get('method1')->inferredType()
                    )
                );
            },
        ];
        yield 'Return type from overridden @method annotation' => [
            <<<'EOT'
                <?php

                use Acme\Post;

                class Barfoo
                {
                    /**
                     * @return AbstractPost
                     */
                    function method1() {}
                }

                /**
                 * @method Post method1()
                 */
                class Foobar extends Barfoo
                {
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                self::assertTrinaryTrue(
                    TypeFactory::class(ClassName::fromString('Acme\Post'))->is(
                        $methods->get('method1')->inferredType()
                    )
                );
            },
        ];
        yield 'Return type from inherited docblock' => [
            <<<'EOT'
                <?php

                use Acme\Post;

                class ParentClass
                {
                    /**
                     * @return \Articles\Blog
                     */
                    function method1() {}
                }

                class Foobar extends ParentClass
                {
                    /**
                     * {@inheritdoc}
                     */
                    function method1() {}
                }
                EOT
        ,
            'Foobar',
            function (ReflectionMethodCollection $methods): void {
                $this->assertEquals('Articles\Blog', $methods->get('method1')->inferredType()->__toString());
            },
        ];
        yield 'Return type from inherited docblock (from interface)' => [
            <<<'EOT'
                <?php

                use Acme\Post;

                interface Barbar
                {
                    /**
                     * @return \Articles\Blog
                     */
                    function method1();
                }

                class Foobar implements Barbar
                {
                    /**
                     * {@inheritdoc}
                     */
                    function method1()
                    {
                    }
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertEquals('Articles\Blog', $methods->get('method1')->inferredType()->__toString());
            },
        ];
        yield 'It reflects an abstract method' => [
            <<<'EOT'
                <?php

                abstract class Foobar
                {
                    abstract public function method();
                    public function methodNonAbstract();
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertTrue($methods->get('method')->isAbstract());
                $this->assertFalse($methods->get('methodNonAbstract')->isAbstract());
            },
        ];
        yield 'It returns the method parameters' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function barfoo($foobar, Barfoo $barfoo, int $number)
                    {
                    }
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertCount(3, $methods->get('barfoo')->parameters());
            },
        ];
        yield 'It returns the nullable parameter types' => [
            <<<'EOT'
                <?php

                namespace Test;

                class Foobar
                {
                    public function barfoo(?Barfoo $barfoo)
                    {
                    }
                }
                EOT
        ,
            'Test\Foobar',
            function ($methods): void {
                $this->assertCount(1, $methods->get('barfoo')->parameters());
                $this->assertEquals(
                    '?Test\Barfoo',
                    $methods->get('barfoo')->parameters()->first()->type()->__toString(),
                );
            },
        ];
        yield 'It tolerantes and logs method parameters with missing variables parameter' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function barfoo(Barfoo = null)
                    {
                    }
                }
                EOT
        ,
            'Foobar',
            function ($methods, LoggerInterface $logger): void {
                $this->assertEquals('', $methods->get('barfoo')->parameters()->first()->name());
                $this->assertStringContainsString(
                    'Parameter has no variable',
                    $logger->messages()[2]
                );
            },
        ];
        yield 'It returns the raw docblock' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * Hello this is a docblock.
                     */
                    public function barfoo($foobar, Barfoo $barfoo, int $number)
                    {
                    }
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertStringContainsString(<<<EOT
                    Hello this is a docblock.
                    EOT
                    , $methods->get('barfoo')->docblock()->raw());
            },
        ];
        yield 'It returns the formatted docblock' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * Hello this is a docblock.
                     *
                     * Yes?
                     */
                    public function barfoo($foobar, Barfoo $barfoo, int $number)
                    {
                    }
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertEquals(<<<EOT
                    Hello this is a docblock.

                    Yes?
                    EOT
                    , $methods->get('barfoo')->docblock()->formatted());
            },
        ];
        yield 'It returns true if the method is static' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public static function barfoo($foobar, Barfoo $barfoo, int $number)
                    {
                    }
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertTrue($methods->get('barfoo')->isStatic());
            },
        ];
        yield 'It returns the method body' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function barfoo()
                    {
                        echo "Hello!";
                    }
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertEquals('echo "Hello!";', (string) $methods->get('barfoo')->body());
            },
        ];
        yield 'It reflects a method from an interface' => [
            <<<'EOT'
                <?php

                interface Foobar
                {
                    public function barfoo()
                    {
                        echo "Hello!";
                    }
                }
                EOT
        ,
            'Foobar',
            function ($methods): void {
                $this->assertTrue($methods->has('barfoo'));
                $this->assertEquals('Foobar', (string) $methods->get('barfoo')->declaringClass()->name());
            },
        ];
        yield 'It reflects a method from a trait' => [
            <<<'EOT'
                <?php

                trait Foobar
                {
                    public function barfoo()
                    {
                        echo "Hello!";
                    }
                }
                EOT
        ,
            'Foobar',
            function (ReflectionMethodCollection $methods): void {
                $this->assertTrue($methods->has('barfoo'));
                $this->assertEquals('Foobar', (string) $methods->get('barfoo')->declaringClass()->name());
            },
        ];
        yield 'It returns methods when a class extends itself' => [
            <<<'EOT'
                <?php

                class Foobar extends Foobar
                {
                    public function barfoo()
                    {
                        echo "Hello!";
                    }
                }
                EOT
        ,
            'Foobar',
            function (ReflectionMethodCollection $methods): void {
                $this->assertTrue($methods->has('barfoo'));
            },
        ];
    }

    /**
     * Note that generics are now resolved during analysis and not statically.
     *
     * @return Generator<mixed>
     */
    public function provideGenerics(): Generator
    {
        yield 'return type from generic' => [
            <<<'PHP'
                <?php

                /**
                 * @template T
                 */
                abstract class Generic {
                    /**
                     * @return T
                     */
                    public function bar() {}
                }

                /**
                 * @extends Generic<Baz>
                 */
                class Foobar extends Generic
                {
                }
                PHP
        ,
            'Foobar',
            function (ReflectionMethodCollection $methods): void {
                self::assertTrue($methods->has('bar'));
                self::assertEquals('T', $methods->get('bar')->inferredType()->__toString());
            },
        ];
        yield 'return type from generic with multiple parameters' => [
            <<<'PHP'
                <?php

                /**
                 * @template T
                 * @template V
                 */
                abstract class Generic {
                    /**
                     * @return V
                     */
                    public function vee() {}
                    /**
                     * @return T
                     */
                    public function tee() {}
                }

                /**
                 * @extends Generic<Boo,Baz>
                 */
                class Foobar extends Generic
                {
                }
                PHP
        ,
            'Foobar',
            function (ReflectionMethodCollection $methods): void {
                self::assertTrue($methods->has('tee'));
                self::assertTrue($methods->has('vee'));
                self::assertEquals('T', $methods->get('tee')->inferredType()->__toString());
                self::assertEquals('V', $methods->get('vee')->inferredType()->__toString());
            },
        ];
        yield 'return type from generic with multiple parameters at a distance' => [
            <<<'PHP'
                <?php

                /**
                 * @template T
                 * @template V
                 */
                abstract class Generic {
                    /**
                     * @return V
                     */
                    public function vee() {}
                    /**
                     * @return T
                     */
                    public function tee() {}
                }


                /**
                 * @template T
                 * @template V
                 * @template G
                 * @extends Generic<T, V>
                 */
                abstract class Middle extends Generic {
                    /** @return G */
                    public function gee() {}
                }

                /**
                 * @extends Middle<Boo,Baz,Bom>
                 */
                class Foobar extends Middle
                {
                }
                PHP
        ,
            'Foobar',
            function (ReflectionMethodCollection $methods): void {
                self::assertTrue($methods->has('tee'));
                self::assertTrue($methods->has('vee'));
                self::assertTrue($methods->has('gee'));
                self::assertEquals('T', $methods->get('tee')->inferredType()->__toString());
                self::assertEquals('V', $methods->get('vee')->inferredType()->__toString());
                self::assertEquals('G', $methods->get('gee')->inferredType()->__toString());
            },
        ];
    }

    /**
     * @return Generator<mixed>
     */
    public function provideDeprecations(): Generator
    {
        yield 'It shows when method is deprecated' => [
            <<<'EOT'
                <?php

                class Foobar extends Foobar
                {
                    /**
                     * @deprecated Foobar this hello
                     */
                    public function barfoo()
                    {
                        echo "Hello!";
                    }
                }
                EOT
        ,
            'Foobar',
            function (ReflectionMethodCollection $methods): void {
                $this->assertTrue($methods->has('barfoo'));
                $this->assertTrue($methods->get('barfoo')->deprecation()->isDefined());
            },
        ];
    }

    /**
     * @dataProvider provideReflectionMethodCollection
     */
    public function testReflectCollection(string $source, string $class, Closure $assertion): void
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class);
    }

    public function provideReflectionMethodCollection(): array
    {
        return [
            'Only methods belonging to a given class' => [
                <<<'EOT'
                    <?php

                    class ParentClass
                    {
                        public function method1() {}
                    }

                    class Foobar extends ParentClass
                    {
                        public function method4() {}
                    }
                    EOT
        ,
            'Foobar',
            function (ReflectionClass $class): void {
                $methods = $class->methods()->belongingTo($class->name());
                $this->assertEquals(
                    ['method4'],
                    $methods->keys()
                );
            },
            ],
        ];
    }
}
