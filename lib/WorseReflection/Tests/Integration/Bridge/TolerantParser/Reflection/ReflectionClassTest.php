<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Phpactor\WorseReflection\Core\Exception\ClassNotFound;
use Generator;
use Phpactor\WorseReflection\Core\Type\UnionType;
use Phpactor\WorseReflection\Core\Visibility;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Name;
use Phpactor\WorseReflection\Core\Reflection\ReflectionClass;
use Phpactor\WorseReflection\Core\Reflection\ReflectionConstant;
use Phpactor\WorseReflection\Core\NameImports;
use Closure;

class ReflectionClassTest extends IntegrationTestCase
{
    public function testExceptionOnClassNotFound(): void
    {
        $this->expectException(ClassNotFound::class);
        $this->createReflector('')->reflectClassLike(ClassName::fromString('Foobar'));
    }

    /**
     * @dataProvider provideReflectionClass
     */
    public function testReflectClass(string $source, string $class, Closure $assertion): void
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class);
    }

    /**
     * @return Generator<string,array{string,string,Closure(ReflectionClass): void}>
     */
    public function provideReflectionClass(): Generator
    {
        yield 'It reflects an empty class' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                }
                EOT
        ,
            'Foobar',
            function ($class): void {
                $this->assertEquals('Foobar', (string) $class->name()->short());
                $this->assertInstanceOf(ReflectionClass::class, $class);
            },
        ];

        yield 'It reflects a class which extends another' => [
            <<<'EOT'
                <?php
                class Barfoo
                {
                }

                class Foobar extends Barfoo
                {
                }
                EOT
        ,
            'Foobar',
            function ($class): void {
                $this->assertEquals('Foobar', (string) $class->name()->short());
                $this->assertEquals('Barfoo', (string) $class->parent()->name()->short());
            },
        ];

        yield 'It reflects class constants' => [
            <<<'EOT'
                <?php

                class Class1
                {
                    const EEEBAR = 'eeebar';
                }

                class Class2 extends Class1
                {
                    const FOOBAR = 'foobar';
                    const BARFOO = 'barfoo';
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertCount(3, $class->constants());
                $this->assertInstanceOf(ReflectionConstant::class, $class->constants()->get('FOOBAR'));
                $this->assertInstanceOf(ReflectionConstant::class, $class->constants()->get('EEEBAR'));
            },
        ];

        yield 'It can provide the name of its last member' => [
            <<<'EOT'
                <?php

                class Class2
                {
                    private $foo;
                    private $bar;
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertEquals('bar', $class->properties()->last()->name());
            },
        ];

        yield 'It can provide the name of its first member' => [
            <<<'EOT'
                <?php

                class Class2
                {
                    private $foo;
                    private $bar;
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertEquals('foo', $class->properties()->first()->name());
            },
        ];

        yield 'It can provide its position' => [
            <<<'EOT'
                <?php

                class Class2
                {
                }

                EOT
        ,
            'Class2',
            function (ReflectionClass $class): void {
                $this->assertEquals(7, $class->position()->start()->toInt());
            },
        ];

        yield 'It can provide the position of its member declarations' => [
            <<<'EOT'
                <?php

                class Class2
                {
                    private $foobar;
                    private $barfoo;

                    public function zed()
                    {
                    }
                }

                EOT
        ,
            'Class2',
            function (ReflectionClass $class): void {
                $this->assertEquals(20, $class->memberListPosition()->start()->toInt());
            },
        ];

        yield 'It provides list of its interfaces' => [
            <<<'EOT'
                <?php

                interface InterfaceOne
                {
                }

                class Class2 implements InterfaceOne
                {
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertEquals(1, $class->interfaces()->count());
                $this->assertEquals('InterfaceOne', $class->interfaces()->first()->name());
            },
        ];

        yield 'It list of interfaces includes interfaces from parent classes' => [
            <<<'EOT'
                <?php

                interface InterfaceOne
                {
                }

                class Class1 implements InterfaceOne
                {
                }

                class Class2 extends Class1
                {
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertEquals(1, $class->interfaces()->count());
                $this->assertEquals('InterfaceOne', $class->interfaces()->first()->name());
            },
        ];

        yield 'It provides list of its traits' => [
            <<<'EOT'
                <?php

                trait TraitNUMBERone
                {
                    }

                trait TraitNUMBERtwo
                {
                }

                class Class2
                {
                    use TraitNUMBERone;
                    use TraitNUMBERtwo;
                }

                EOT
        ,
            'Class2',
            function (ReflectionClass $class): void {
                $this->assertEquals(2, $class->traits()->count());
                $this->assertEquals('TraitNUMBERone', $class->traits()->get('TraitNUMBERone')->name());
                $this->assertEquals('TraitNUMBERtwo', $class->traits()->get('TraitNUMBERtwo')->name());
            },
        ];

        yield 'Traits are inherited from parent classes (?)' => [
            <<<'EOT'
                <?php

                trait TraitNUMBERone
                {
                }

                class Class2
                {
                    use TraitNUMBERone;
                }

                class Class1 extends Class2
                {
                }

                EOT
        ,
            'Class1',
            function ($class): void {
                $this->assertEquals(1, $class->traits()->count());
                $this->assertEquals('TraitNUMBERone', $class->traits()->first()->name());
            },
        ];

        yield 'Get methods includes trait methods' => [
            <<<'EOT'
                <?php

                trait TraitNUMBERone
                {
                    public function traitMethod1()
                    {
                    }
                    }

                trait TraitNUMBERtwo
                {
                    public function traitMethod2()
                    {
                    }
                }

                class Class2
                {
                    use TraitNUMBERone, TraitNUMBERtwo;

                    public function notATrait()
                    {
                    }
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertEquals(3, $class->methods()->count());
                $this->assertTrue($class->methods()->has('traitMethod1'));
                $this->assertTrue($class->methods()->has('traitMethod2'));
            },
        ];

        yield 'Tolerates not found traits' => [
            <<<'EOT'
                <?php

                class Class2
                {
                    use TraitNUMBERone, TraitNUMBERtwo;

                    public function notATrait()
                    {
                    }
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertEquals(1, $class->methods()->count());
            },
        ];

        yield 'Get methods includes aliased trait methods' => [
            <<<'EOT'
                <?php

                trait TraitOne
                {
                    public function one() {}
                    public function three() {}
                    public function four() {}
                }

                class Class2
                {
                    use TraitOne {
                        one as private two;
                        three as protected three;
                    }

                    public function one()
                    {
                    }
                }

                EOT
        ,
            'Class2',
            function (ReflectionClass $class): void {
                $this->assertEquals(4, $class->methods()->count());
                $this->assertTrue($class->methods()->has('one'));
                $this->assertTrue($class->methods()->has('two'));
                $this->assertTrue($class->methods()->has('three'));
                $this->assertTrue($class->methods()->has('four'));
                $this->assertEquals(Visibility::private(), $class->methods()->get('two')->visibility());
                $this->assertEquals(Visibility::protected(), $class->methods()->get('three')->visibility());
                $this->assertFalse($class->methods()->belongingTo(ClassName::fromString('Class2'))->has('two'));
                $this->assertEquals('TraitOne', $class->methods()->get('two')->declaringClass()->name()->short());
            },
        ];

        yield 'Get methods includes namespaced aliased trait methods' => [
            <<<'EOT'
                <?php

                namespace Bar;

                trait TraitOne
                {
                    public function one() {}
                    public function three() {}
                }

                class Class2
                {
                    use \Bar\TraitOne {
                        one as private two;
                        three as protected three;
                    }

                    public function one()
                    {
                    }
                }

                EOT
        ,
            'Bar\Class2',
            function (ReflectionClass $class): void {
                $this->assertEquals(3, $class->methods()->count());
                $this->assertTrue($class->methods()->has('one'));
                $this->assertTrue($class->methods()->has('three'));
            },
        ];

        yield 'Get trait properties' => [
            <<<'EOT'
                <?php

                trait TraitNUMBERone
                {
                    private $prop1;
                }

                class Class2
                {
                    use TraitNUMBERone;
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertEquals(1, $class->properties()->count());
                $this->assertEquals('prop1', $class->properties()->first()->name());
            },
        ];

        yield 'Get methods at offset' => [
            <<<'EOT'
                <?php

                class Class2
                {
                    public function notATrait()
                    {
                    }
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertEquals(1, $class->methods()->atOffset(27)->count());
            },
        ];

        yield 'Get properties includes trait properties' => [
            <<<'EOT'
                <?php

                trait TraitNUMBERone
                {
                    public $foobar;
                }

                class Class2
                {
                    use TraitNUMBERone;

                    private $notAFoobar;
                }

                EOT
        ,
            'Class2',
            function (ReflectionClass $class): void {
                $this->assertEquals(2, $class->properties()->count());
                $this->assertEquals('foobar', $class->properties()->first()->name());
            },
        ];

        yield 'Get properties for belonging to' => [
            <<<'EOT'
                <?php

                class Class1
                {
                    public $foobar;
                }

                class Class2 extends Class1
                {
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertCount(1, $class->properties()->belongingTo(ClassName::fromString('Class1')));
                $this->assertCount(0, $class->properties()->belongingTo(ClassName::fromString('Class2')));
            },
        ];


        yield 'If it extends an interface, then ignore' => [
            <<<'EOT'
                <?php

                interface SomeInterface
                {
                }

                class Class2 extends SomeInterface
                {
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertEquals(0, $class->methods()->count());
            },
        ];


        yield 'isInstanceOf returns false when it is not an instance of' => [
            <<<'EOT'
                <?php

                class Class2
                {
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertFalse($class->isInstanceOf(ClassName::fromString('Foobar')));
            },
        ];

        yield 'isInstanceOf returns true for itself' => [
            <<<'EOT'
                <?php

                class Class2
                {
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertTrue($class->isInstanceOf(ClassName::fromString('Class2')));
            },
        ];

        yield 'isInstanceOf returns true when it is not an instance of an interface' => [
            <<<'EOT'
                <?php

                interface SomeInterface
                {
                }

                class Class2 implements SomeInterface
                {
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertTrue($class->isInstanceOf(ClassName::fromString('SomeInterface')));
            },
        ];

        yield 'isInstanceOf returns true when a class implements the interface and has a parent' => [
            <<<'EOT'
                <?php

                interface SomeInterface {}

                class ParentClass {}

                class Class2 extends ParentClass implements SomeInterface {}

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertTrue($class->isInstanceOf(ClassName::fromString('SomeInterface')));
            },
        ];

        yield 'isInstanceOf returns true for a parent class' => [
            <<<'EOT'
                <?php

                class SomeParent
                {
                }

                class Class2 extends SomeParent
                {
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertTrue($class->isInstanceOf(ClassName::fromString('SomeParent')));
            },
        ];

        yield 'Returns source code' => [
            <<<'EOT'
                <?php

                class Class2
                {
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertStringContainsString('class Class2', (string) $class->sourceCode());
            },
        ];

        yield 'Returns imported classes' => [
            <<<'EOT'
                <?php

                use Foobar\Barfoo;
                use Barfoo\Foobaz as Carzatz;

                class Class2
                {
                }

                EOT
        ,
            'Class2',
            function ($class): void {
                $this->assertEquals(NameImports::fromNames([
                    'Barfoo' => Name::fromString('Foobar\\Barfoo'),
                    'Carzatz' => Name::fromString('Barfoo\\Foobaz'),
                ]), $class->scope()->nameImports());
            },
        ];

        yield 'Inherits constants from interface' => [
            <<<'EOT'
                <?php

                use Foobar\Barfoo;
                use Barfoo\Foobaz as Carzatz;

                interface SomeInterface
                {
                    const SOME_CONSTANT = 'foo';
                }

                class Class2 implements SomeInterface
                {
                }

                EOT
        ,
            'Class2',
            function (ReflectionClass $class): void {
                $this->assertCount(1, $class->constants());
                $this->assertEquals('SOME_CONSTANT', $class->constants()->get('SOME_CONSTANT')->name());
            },
        ];

        yield 'Returns all members' => [
            <<<'EOT'
                <?php

                class Class1
                {
                    private const FOOBAR = 'foobar';
                    private $foo;
                    private function foobar() {}
                }

                EOT
        ,
            'Class1',
            function (ReflectionClass $class): void {
                $this->assertCount(3, $class->members());
                $this->assertTrue($class->members()->has('FOOBAR'));
                $this->assertTrue($class->members()->has('foobar'));
                $this->assertTrue($class->members()->has('foo'));
            },
        ];

        yield 'Incomplete extends' => [
            <<<'EOT'
                <?php

                class Class1 extends
                {
                }

                EOT
        ,
            'Class1',
            function (ReflectionClass $class): void {
                $this->assertNull($class->parent());
                $this->assertEquals('Class1', $class->name()->short());
            },
        ];

        yield 'Does not infinite loop with self-referencing class on get interfaces' => [
            <<<'EOT'
                <?php

                class Class1 extends Class1
                {
                }

                EOT
        ,
            'Class1',
            function (ReflectionClass $class): void {
                $this->assertCount(0, $class->interfaces());
            },
        ];

        yield 'Says if class is abstract' => [
            <<<'EOT'
                <?php

                abstract class Class1
                {
                }

                EOT
        ,
            'Class1',
            function (ReflectionClass $class): void {
                $this->assertTrue($class->isAbstract());
            },
        ];

        yield 'Says if class is not abstract' => [
            <<<'EOT'
                <?php

                class Class1
                {
                }

                EOT
        ,
            'Class1',
            function (ReflectionClass $class): void {
                $this->assertFalse($class->isAbstract());
            },
        ];

        yield 'Says if class is final' => [
            <<<'EOT'
                <?php

                final class Class1
                {
                }

                EOT
        ,
            'Class1',
            function (ReflectionClass $class): void {
                $this->assertTrue($class->isFinal());
            },
        ];

        yield 'Says if class is deprecated' => [
            <<<'EOT'
                <?php

                /**
                 * @deprecated Foobar yes
                 */
                final class Class1
                {
                }

                EOT
        ,
            'Class1',
            function (ReflectionClass $class): void {
                $this->assertTrue($class->deprecation()->isDefined());
            },
        ];
    }

    /**
     * @dataProvider provideVirtualMethods
     */
    public function testVirtualMethods(string $source, string $class, Closure $assertion): void
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class);
    }

    /**
     * @return Generator<string,array{string,string,Closure(ReflectionClass): void}>
     */
    public function provideVirtualMethods(): Generator
    {
        yield 'virtual methods' => [
            <<<'EOT'
                <?php

                /**
                 * @method \Foobar foobar()
                 * @method \Foobar barfoo()
                 */
                class Class1
                {
                }

                EOT
        ,
            'Class1',
            function ($class): void {
                $this->assertEquals(2, $class->methods()->count());
                $this->assertEquals('foobar', $class->methods()->first()->name());
            }
        ];

        yield 'virtual methods merge onto existing ones' => [
            <<<'EOT'
                <?php

                /**
                 * @method \Foobar foobar()
                 */
                class Class1
                {
                    public function foobar(): \Barfoo
                    {
                    }
                }

                EOT
        ,
            'Class1',
            function (ReflectionClass $class): void {
                $this->assertCount(1, $class->methods());

                // originally this returned the declared type
                $this->assertEquals(
                    'Foobar',
                    $class->methods()->first()->type()->__toString(),
                );
                $this->assertEquals(
                    'Foobar',
                    $class->methods()->first()->inferredType()->__toString(),
                );
            },
        ];

        yield 'virtual methods are inherited' => [
            <<<'EOT'
                <?php


                /** @method \Foobar foobar() */
                class Class1 {}

                class Class2 extends Class1 {
                    public function foo()  {}
                }

                EOT
        ,
            'Class2',
            function (ReflectionClass $class): void {
                $this->assertCount(2, $class->methods());
                $this->assertEquals(
                    'Foobar',
                    $class->methods()->get('foobar')->inferredType()->__toString()
                );
            },
        ];

        yield 'virtual methods are inherited from interface' => [
            <<<'EOT'
                <?php


                /** @method \Foobar foobar() */
                interface Class1 {}

                class Class2 implements Class1 {
                }

                EOT
        ,
            'Class2',
            function (ReflectionClass $class): void {
                $this->assertCount(1, $class->methods());
                $this->assertEquals(
                    'Foobar',
                    $class->methods()->get('foobar')->inferredType()->__toString()
                );
            },
        ];

        yield 'virtual methods are inherited from multiple layers of interfaces' => [
            <<<'EOT'
                <?php

                /** @method \Foobar foobar() */
                interface Class3 {}

                interface Class1 extends Class3 {}

                class Class2 implements Class1 {
                }

                EOT
        ,
            'Class2',
            function (ReflectionClass $class): void {
                $this->assertCount(1, $class->methods());
                $this->assertEquals(
                    'Foobar',
                    $class->methods()->get('foobar')->inferredType()->__toString()
                );
            },
        ];

        yield 'virtual methods are inherited from parent class which implements interface' => [
            <<<'EOT'
                <?php

                /** @method \Foobar foobar() */
                interface ParentInterface {}

                class ParentClass implements ParentInterface {}

                class Class2 extends ParentClass {
                }

                EOT
        ,
            'Class2',
            function (ReflectionClass $class): void {
                $this->assertCount(1, $class->methods());
                $this->assertEquals(
                    'Foobar',
                    $class->methods()->get('foobar')->inferredType()->__toString()
                );
                $this->assertEquals(
                    'ParentInterface',
                    $class->methods()->get('foobar')->declaringClass()->name()->__toString()
                );
            },
        ];

        yield 'virtual method types can be relative' => [
            '<?php namespace Bosh { /** @method Foobar foobar() */ class Class1 {}',
            'Bosh\Class1',
            function (ReflectionClass $class): void {
                $this->assertEquals(
                    'Bosh\Foobar',
                    $class->methods()->get('foobar')->inferredType()->__toString()
                );
            },
        ];

        yield 'virtual method types can be absolute' => [
            '<?php namespace Bosh { /** @method \Foobar foobar() */ class Class1 {}',
            'Bosh\Class1',
            function (ReflectionClass $class): void {
                $this->assertEquals(
                    'Foobar',
                    $class->methods()->get('foobar')->inferredType()->__toString()
                );
            },
        ];

        yield 'virtual methods of child classes override those of parents' => [
            <<<'EOT'
                <?php


                /** @method \Foobar foobar() */
                class Class1 {}

                /** @method \Barfoo foobar() */
                class Class2 extends Class1 {
                    public function foo()  {}
                }

                EOT
        ,
            'Class2',
            function (ReflectionClass $class): void {
                $this->assertCount(2, $class->methods());
                $this->assertEquals(
                    'Barfoo',
                    $class->methods()->get('foobar')->inferredType()->__toString()
                );
            },
        ];

        yield 'virtual methods are extracted from traits' => [
            <<<'EOT'
                <?php

                /**
                 * @method \Foobar foobar()
                 */
                trait Trait1 {
                }

                class Class1
                {
                    use Trait1;
                }

                EOT
        ,
            'Class1',
            function (ReflectionClass $class): void {
                $this->assertCount(1, $class->methods());
                $this->assertEquals('Foobar', $class->methods()->first()->inferredType()->__toString());
            },
        ];

        yield 'virtual methods are extracted from traits of a parent class' => [
            <<<'EOT'
                <?php

                /**
                 * @method \Foobar foobar()
                 */
                trait Trait1 {
                }

                class Class2
                {
                    use Trait1;
                }

                class Class1 extends Class2
                {
                }

                EOT
        ,
            'Class1',
            function (ReflectionClass $class): void {
                $this->assertCount(1, $class->methods());
                $this->assertEquals('Foobar', $class->methods()->first()->inferredType()->__toString());
            },
        ];
    }

    /**
     * @dataProvider provideVirtualProperties
     */
    public function testVirtualProperties(string $source, string $class, Closure $assertion): void
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class);
    }

    /**
     * @return Generator<string,array{string,string,Closure(ReflectionClass): void}>
     */
    public function provideVirtualProperties(): Generator
    {
        yield 'virtual properties' => [
            <<<'EOT'
                <?php

                /**
                 * @property \Foobar $foobar
                 * @property \Foobar $barfoo
                 */
                class Class1
                {
                }

                EOT
        ,
            'Class1',
            function ($class): void {
                $this->assertEquals(2, $class->properties()->count());
                $this->assertEquals('foobar', $class->properties()->first()->name());
            }
        ];

        yield 'invalid properties' => [
            <<<'EOT'
                <?php

                /**
                 * @property $foobar
                 * @property
                 */
                class Class1
                {
                }

                EOT
        ,
            'Class1',
            function (ReflectionClass $class): void {
                $this->assertEquals(2, $class->properties()->count());
            }
        ];

        yield 'multiple types' => [
            <<<'EOT'
                <?php

                /**
                 * @property string|int $foobar
                 */
                class Class1
                {
                }

                EOT
        ,
            'Class1',
            function (ReflectionClass $class): void {
                $this->assertEquals(1, $class->properties()->count());
                self::assertInstanceOf(UnionType::class, $class->properties()->first()->type());
                self::assertEquals('string|int', $class->properties()->first()->type());
            }
        ];

        yield 'virtual properties are extracted from traits' => [
            <<<'EOT'
                <?php

                /**
                 * @property \Foobar $foobar
                 * @property \Barfoo $barfoo
                 */
                trait Trait1 {
                }

                class Class1
                {
                    use Trait1;
                }

                EOT
        ,
            'Class1',
            function (ReflectionClass $class): void {
                $this->assertEquals(2, $class->properties()->count());
                $this->assertEquals('foobar', $class->properties()->first()->name());
                $this->assertEquals('Foobar', $class->properties()->first()->inferredType()->__toString());
                $this->assertEquals('barfoo', $class->properties()->last()->name());
                $this->assertEquals('Barfoo', $class->properties()->last()->inferredType()->__toString());
            }
        ];

        yield 'virtual properties are extracted from traits of a parent class' => [
            <<<'EOT'
                <?php

                /**
                 * @property \Foobar $foobar
                 * @property \Barfoo $barfoo
                 */
                trait Trait1 {
                }

                class Class2
                {
                    use Trait1;
                }

                class Class1 extends Class2
                {
                }

                EOT
        ,
            'Class1',
            function (ReflectionClass $class): void {
                $this->assertEquals(2, $class->properties()->count());
                $this->assertEquals('foobar', $class->properties()->first()->name());
                $this->assertEquals('Foobar', $class->properties()->first()->inferredType()->__toString());
                $this->assertEquals('barfoo', $class->properties()->last()->name());
                $this->assertEquals('Barfoo', $class->properties()->last()->inferredType()->__toString());
            }
        ];
    }
}
