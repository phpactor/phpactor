<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Reflection;

use Generator;
use Phpactor\WorseReflection\Tests\Integration\IntegrationTestCase;
use Phpactor\WorseReflection\Core\ClassName;
use Phpactor\WorseReflection\Core\Reflection\ReflectionInterface;
use Phpactor\WorseReflection\Core\Reflection\ReflectionConstant;
use Closure;

class ReflectionInterfaceTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideReflectionInterface
     */
    public function testReflectInterface(string $source, string $class, Closure $assertion): void
    {
        $class = $this->createReflector($source)->reflectClassLike(ClassName::fromString($class));
        $assertion($class);
    }

    /**
     * @return Generator<array{string,string,Closure(ReflectionInterface): void}>
     */
    public function provideReflectionInterface(): Generator
    {
        yield 'It reflects an interface' => [
            <<<'EOT'
                <?php

                interface Barfoo
                {
                }
                EOT
        , 'Barfoo',
            function ($class): void {
                $this->assertEquals('Barfoo', (string) $class->name()->short());
                $this->assertInstanceOf(ReflectionInterface::class, $class);
            },
        ];

        yield 'It reflects a classes interfaces' => [
            <<<'EOT'
                <?php
                interface Barfoo
                {
                }

                interface Bazbar
                {
                }

                class Foobar implements Barfoo, Bazbar
                {
                }
                EOT
        ,
            'Foobar',
            function ($class): void {
                $interfaces = $class->interfaces();
                $this->assertCount(2, $interfaces);
                $interface = $interfaces->get('Barfoo');
                $this->assertInstanceOf(ReflectionInterface::class, $interface);
            },
        ];

        yield 'It reflects a class which implements an interface which extends other interfaces' => [
            <<<'EOT'
                <?php
                interface Barfoo
                {
                }

                interface Zedboo
                {
                }

                interface Bazbar extends Barfoo, Zedboo
                {
                }
                EOT
        ,
            'Bazbar',
            function ($class): void {
                $interfaces = $class->parents();
                $this->assertCount(2, $interfaces);
                $interface = $interfaces->get('Barfoo');
                $this->assertInstanceOf(ReflectionInterface::class, $interface);
            },
        ];

        yield 'It reflects inherited methods in an interface' => [
            <<<'EOT'
                <?php
                interface Barfoo
                {
                    public function methodOne();
                }

                interface Zedboo
                {
                    public function methodTwo();
                }

                interface Bazbar extends Barfoo, Zedboo
                {
                }
                EOT
        ,
            'Bazbar',
            function ($interface): void {
                $this->assertInstanceOf(ReflectionInterface::class, $interface);
                $this->assertCount(2, $interface->methods());
            },
        ];

        yield 'It reflect interface methods' => [
            <<<'EOT'
                <?php

                interface Barfoo
                {
                    public function foobar();
                }
                EOT
        ,
            'Barfoo',
            function ($class): void {
                $this->assertEquals('Barfoo', (string) $class->name()->short());
                $this->assertEquals(['foobar'], $class->methods()->keys());
            },
        ];

        yield 'It interface constants' => [
            <<<'EOT'
                <?php

                interface Int1
                {
                    const FOOBAR = 'foobar';
                }

                interface Int2
                {
                    const FOOBAR = 'foobar';
                    const BARFOO = 'barfoo';
                }

                interface Int3 extends Int1, Int2
                {
                    const EEEBAR = 'eeebar';
                }
                EOT
        ,
            'Int3',
            function ($class): void {
                $this->assertCount(3, $class->constants());
                $this->assertInstanceOf(ReflectionConstant::class, $class->constants()->get('FOOBAR'));
                $this->assertInstanceOf(ReflectionConstant::class, $class->constants()->get('EEEBAR'));
            },
        ];

        yield 'instanceof' => [
            <<<'EOT'
                <?php
                interface Interface1
                {
                }

                interface Interface2 extends Interface1
                {
                }
                EOT
        ,
            'Interface2',
            function ($class): void {
                $this->assertTrue($class->isInstanceOf(ClassName::fromString('Interface2')));
                $this->assertTrue($class->isInstanceOf(ClassName::fromString('Interface1')));
                $this->assertFalse($class->isInstanceOf(ClassName::fromString('Interface3')));
            },
        ];

        yield 'Method class is of context class, not declaration class' => [
            <<<'EOT'
                <?php

                namespace Acme;

                interface Barfoo
                {
                    function method1() {}
                }

                interface Foobar extends Barfoo
                {
                }
                EOT
        ,
            'Acme\Foobar',
            function (ReflectionInterface $class): void {
                $this->assertEquals(
                    'Acme\Foobar',
                    (string) $class->methods()->get('method1')->class()->name()
                );
                $this->assertEquals(
                    'Acme\Barfoo',
                    (string) $class->methods()->get('method1')->declaringClass()->name()
                );
            },
        ];

        yield 'Returns all members' => [
            <<<'EOT'
                <?php

                interface Class1
                {
                    private const FOOBAR = 'foobar';
                    private function foobar() {}
                }

                EOT
        ,
            'Class1',
            function (ReflectionInterface $class): void {
                $this->assertCount(2, $class->members());
                $this->assertTrue($class->members()->has('FOOBAR'));
                $this->assertTrue($class->members()->has('foobar'));
            },
        ];
    }
}
