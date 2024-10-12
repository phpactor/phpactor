<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Reflection\Collection\ReflectionPropertyCollection as PhpactorReflectionPropertyCollection;
use Phpactor\WorseReflection\Core\TypeFactory;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Core\Reflection\ReflectionProperty;
use Closure;
use Generator;

class ReflectionPropertyTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionPropertyTypes
     * @dataProvider provideReflectionProperty
     */
    public function testReflectProperty(string $source, string $class, Closure $assertion): void
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class->properties());
    }

    /**
     * @return Generator<string,array{string,string,Closure(ReflectionProperty): void}>
     */
    public function provideReflectionPropertyTypes(): Generator
    {
        yield 'It reflects a property with union type' => [
            '<?php class Foobar { private int|string $property;}',
                'Foobar',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertEquals('property', $properties->get('property')->name());
                    $this->assertEquals(TypeFactory::union(...[
                        TypeFactory::int(),
                        TypeFactory::string(),
                    ]), $properties->get('property')->inferredType());
                },
        ];
    }

    /**
     * @return Generator<string,array{string,string,Closure(ReflectionProperty): void}>
     */
    public function provideReflectionProperty()
    {
        yield 'It reflects a property' => [
                <<<'EOT'
                    <?php

                    class Foobar
                    {
                        public $property;
                    }
                    EOT
                ,
                'Foobar',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertEquals('property', $properties->get('property')->name());
                    $this->assertInstanceOf(ReflectionProperty::class, $properties->get('property'));
                },
            ];

        yield 'Private visibility' => [
                <<<'EOT'
                    <?php

                    class Foobar
                    {
                        private $property;
                    }
                    EOT
                ,
                'Foobar',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertEquals(Visibility::private(), $properties->get('property')->visibility());
                },
            ];

        yield 'Protected visibility' => [
                <<<'EOT'
                    <?php

                    class Foobar
                    {
                        protected $property;
                    }
                    EOT
                ,
                'Foobar',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertEquals(Visibility::protected(), $properties->get('property')->visibility());
                },
            ];

        yield 'Public visibility' => [
                <<<'EOT'
                    <?php

                    class Foobar
                    {
                        public $property;
                    }
                    EOT
                ,
                'Foobar',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertEquals(Visibility::public(), $properties->get('property')->visibility());
                },
            ];

        yield 'Inherited properties' => [
                <<<'EOT'
                    <?php

                    class ParentParentClass extends NonExisting
                    {
                        public $property5;
                    }

                    class ParentClass extends ParentParentClass
                    {
                        private $property1;
                        protected $property2;
                        public $property3;
                        public $property4;
                    }

                    class Foobar extends ParentClass
                    {
                        public $property4; // overrides from previous
                    }
                    EOT
                ,
                'Foobar',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertEquals(
                        ['property5', 'property2', 'property3', 'property4'],
                        $properties->keys()
                    );
                    self::assertEquals(
                        'ParentParentClass',
                        $properties->get('property5')->declaringClass()->name()->head()->__toString()
                    );
                    self::assertEquals('Foobar', $properties->get('property5')->class()->name()->head()->__toString());
                },
            ];

        yield 'Return type from docblock' => [
                <<<'EOT'
                    <?php

                    use Acme\Post;

                    class Foobar
                    {
                        /**
                         * @var Post
                         */
                        private $property1;
                    }
                    EOT
                ,
                'Foobar',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertEquals(
                        'Acme\Post',
                        $properties->get('property1')->inferredType()->__toString(),
                    );
                    $this->assertFalse($properties->get('property1')->isStatic());
                },
            ];

        yield 'Returns unknown type for (real) type' => [
                <<<'EOT'
                    <?php

                    use Acme\Post;

                    class Foobar
                    {
                        /**
                         * @var Post
                         */
                        private $property1;
                    }
                    EOT
                ,
                'Foobar',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertEquals(
                        TypeFactory::unknown(),
                        $properties->get('property1')->type()
                    );
                },
            ];

        yield 'Property with assignment' => [
                <<<'EOT'
                    <?php

                    use Acme\Post;

                    class Foobar
                    {
                        private $property1 = 'bar';
                    }
                    EOT
                ,
                'Foobar',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertTrue($properties->has('property1'));
                },
            ];

        yield 'Return true if property is static' => [
                <<<'EOT'
                    <?php

                    use Acme\Post;

                    class Foobar
                    {
                        private static $property1;
                    }
                    EOT
                ,
                'Foobar',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertTrue($properties->get('property1')->isStatic());
                },
            ];

        yield 'Returns declaring class' => [
                <<<'EOT'
                    <?php

                    class Foobar
                    {
                        private $property1;
                    }
                    EOT
                ,
                'Foobar',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertEquals('Foobar', $properties->get('property1')->declaringClass()->name()->__toString());
                },
            ];

        yield 'Property type from class @property annotation' => [
                <<<'EOT'
                    <?php

                    use Acme\Post;

                    /**
                     * @property string $bar
                     */
                    class Foobar
                    {
                        private $bar;
                    }
                    EOT
                ,
                'Foobar',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertEquals(TypeFactory::fromString('string'), $properties->get('bar')->inferredType());
                },
            ];

        yield 'Property type from class @property annotation with imported name' => [
                <<<'EOT'
                    <?php

                    use Acme\Post;
                    use Bar\Foo;

                    /**
                     * @property Foo $bar
                     */
                    class Foobar
                    {
                        private $bar;
                    }
                    EOT
                ,
                'Foobar',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertEquals('Bar\Foo', $properties->get('bar')->inferredType()->__toString());
                },
            ];

        yield 'Property type from parent class @property annotation with imported name' => [
                <<<'EOT'
                    <?php

                    use Acme\Post;
                    use Bar\Foo;

                    /**
                     * @property Foo $bar
                     */
                    class Barfoo
                    {
                        protected $bar;
                    }

                    class Foobar extends Barfoo
                    {
                    }
                    EOT
                ,
                'Foobar',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertEquals('Bar\Foo', $properties->get('bar')->inferredType()->__toString());
                },
            ];

        yield 'Typed property from imported class' => [
                <<<'EOT'
                    <?php

                    namespace Test;

                    use Acme\Post;
                    use Bar\Foo;

                    class Barfoo
                    {
                         public Foo $bar;
                         public string $baz;
                         public $undefined;
                         public iterable $it;

                         /** @var Foo[] */
                         public iterable $collection;
                    }
                    EOT
                ,
                'Test\Barfoo',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertEquals('Bar\Foo', $properties->get('bar')->type()->__toString());
                    $this->assertEquals('Bar\Foo', $properties->get('bar')->inferredType()->__toString());

                    $this->assertEquals(TypeFactory::string(), $properties->get('baz')->type());

                    $this->assertEquals(TypeFactory::undefined(), $properties->get('undefined')->type());

                    $this->assertEquals(TypeFactory::iterable(), $properties->get('collection')->type());
                    $this->assertEquals('Bar\Foo[]', $properties->get('collection')->inferredType()->__toString());
                    $this->assertEquals(
                        TypeFactory::iterable(),
                        $properties->get('it')->type()
                    );
                },
            ];

        yield 'Nullable typed property' => [
                <<<'EOT'
                    <?php

                    namespace Test;

                    class Barfoo
                    {
                         public ?string $foo;
                    }
                    EOT
                ,
                'Test\Barfoo',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertEquals(
                        TypeFactory::fromString('?string'),
                        $properties->get('foo')->type()
                    );
                },
        ];

        yield 'Property with intersection' => [
                <<<'EOT'
                    <?php

                    namespace Test;

                    class Barfoo
                    {
                         public Foo&Bar $foo;
                    }
                    EOT
                ,
                'Test\Barfoo',
                function (PhpactorReflectionPropertyCollection $properties): void {
                    $this->assertEquals(
                        TypeFactory::intersection(
                            TypeFactory::class('Test\Foo'),
                            TypeFactory::class('Test\Bar'),
                        )->__toString(),
                        $properties->get('foo')->type()->__toString()
                    );
                },
            ];
    }
}
