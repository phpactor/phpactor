<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Transformer;

use Generator;
use Phpactor\CodeTransform\Domain\SourceCode;

use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\CompleteConstructor;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use function Amp\Promise\wait;

class CompleteConstructorTest extends WorseTestCase
{
    /**
     * @dataProvider provideDiagnostics
     */
    public function testDiagnostics(string $example, int $expectedCount): void
    {
        $source = SourceCode::fromString($example);
        $transformer = new CompleteConstructor($this->reflectorForWorkspace($example), $this->updater(), 'private', promote: false);
        $this->assertCount($expectedCount, wait($transformer->diagnostics($source)));
    }
    /**
     * @return Generator<string,array{string,int}>
     */
    public function provideDiagnostics(): Generator
    {
        yield 'empty' => [
            <<<'EOT'
                <?php
                EOT
        , 0
        ];

        yield 'unassigned constructor' => [
            <<<'EOT'
                <?php class Foo { function __construct($string) {} }
                EOT
        , 1
        ];

        yield 'assigned constructor without property' => [
            <<<'EOT'
                <?php class Foo { function __construct($string) { $this->string = $string; } }
                EOT
        , 1
        ];
    }

    /**
     * @dataProvider provideCompleteConstructor
     */
    public function testCompleteConstructor(string $example, string $expected): void
    {
        $source = SourceCode::fromString($example);
        $transformer = new CompleteConstructor($this->reflectorForWorkspace($example), $this->updater(), 'private');
        $transformed = wait($transformer->transform($source));
        $this->assertEquals((string) $expected, (string) $transformed->apply($source));
    }
    /**
     * @return Generator<string,array{string,string}>
     */
    public function provideCompleteConstructor(): Generator
    {
        yield 'It does nothing on source with no classes' => [
            <<<'EOT'
                <?php
                EOT
        ,
            <<<'EOT'
                <?php
                EOT

        ];

        yield  'It does nothing on an empty constructor' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function __construct()
                    {
                    }
                }
                EOT
        ,
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function __construct()
                    {
                    }
                }
                EOT

        ];

        yield  'It does nothing on an empty constructor in currency class' => [
            <<<'EOT'
                <?php
                class Barfoo
                {
                    private $bar;
                    public function __construct(string $bar)
                    {
                        $this->bar = $bar;
                    }
                }

                class Foobar extends Barfoo
                {
                }
                EOT
        ,
            <<<'EOT'
                <?php
                class Barfoo
                {
                    private $bar;
                    public function __construct(string $bar)
                    {
                        $this->bar = $bar;
                    }
                }

                class Foobar extends Barfoo
                {
                }
                EOT
        ];

        yield  'It does nothing with no constructor' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                }
                EOT
        ,
            <<<'EOT'
                <?php

                class Foobar
                {
                }
                EOT

        ];

        yield  'It does adds assignations and properties' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function __construct($foo, $bar)
                    {
                    }
                }
                EOT
        ,
            <<<'EOT'
                <?php

                class Foobar
                {
                    private $foo;
                    private $bar;

                    public function __construct($foo, $bar)
                    {
                        $this->foo = $foo;
                        $this->bar = $bar;
                    }
                }
                EOT

        ];

        yield  'adds assignations and properties on abstract class' => [
            <<<'EOT'
                <?php

                abstract class Foobar
                {
                    public function __construct($foo, $bar)
                    {
                    }
                }
                EOT
        ,
            <<<'EOT'
                <?php

                abstract class Foobar
                {
                    private $foo;
                    private $bar;

                    public function __construct($foo, $bar)
                    {
                        $this->foo = $foo;
                        $this->bar = $bar;
                    }
                }
                EOT

        ];

        yield  'It adds type docblocks' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function __construct(string $foo, Foobar $bar)
                    {
                    }
                }
                EOT
        ,
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @var string
                     */
                    private $foo;
                    /**
                     * @var Foobar
                     */
                    private $bar;

                    public function __construct(string $foo, Foobar $bar)
                    {
                        $this->foo = $foo;
                        $this->bar = $bar;
                    }
                }
                EOT

        ];

        yield  'It does adds nullable type docblocks' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function __construct(?string $foo)
                    {
                    }
                }
                EOT
        ,
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @var ?string
                     */
                    private $foo;

                    public function __construct(?string $foo)
                    {
                        $this->foo = $foo;
                    }
                }
                EOT

        ];

        yield  'Adds documented types' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @param Foo<class-string,Bar> $foo
                     */
                    public function __construct(array $foo)
                    {
                    }
                }
                EOT
        ,
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @var Foo<class-string,Bar>
                     */
                    private $foo;

                    /**
                     * @param Foo<class-string,Bar> $foo
                     */
                    public function __construct(array $foo)
                    {
                        $this->foo = $foo;
                    }
                }
                EOT

        ];

        yield  'It is idempotent' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @var string
                     */
                    private $foo;

                    public function __construct(string $foo)
                    {
                        $this->foo = $foo;
                    }
                }
                EOT
        ,
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @var string
                     */
                    private $foo;

                    public function __construct(string $foo)
                    {
                        $this->foo = $foo;
                    }
                }
                EOT

        ];

        yield  'It is updates missing' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @var string
                     */
                    private $foo;

                    public function __construct(string $foo, Acme $acme)
                    {
                        $this->foo = $foo;
                    }
                }
                EOT
        ,
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @var string
                     */
                    private $foo;

                    /**
                     * @var Acme
                     */
                    private $acme;


                    public function __construct(string $foo, Acme $acme)
                    {
                        $this->foo = $foo;
                        $this->acme = $acme;
                    }
                }
                EOT

        ];

        yield  'It does not redeclare' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @var string
                     */
                    private $foo;

                    public function __construct(string $foo)
                    {
                        $this->foo = $foo ?: null;
                    }
                }
                EOT
        ,
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @var string
                     */
                    private $foo;

                    public function __construct(string $foo)
                    {
                        $this->foo = $foo ?: null;
                    }
                }
                EOT

        ];

        yield  'Existing property with assignment' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @var string
                     */
                    private $foo = false;

                    public function __construct($bar)
                    {
                    }
                }
                EOT
        ,
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @var string
                     */
                    private $foo = false;
                    private $bar;


                    public function __construct($bar)
                    {
                        $this->bar = $bar;
                    }
                }
                EOT

        ];

        yield  'Aliased import' => [
            <<<'EOT'
                <?php

                use stdClass as Foobar;

                class Foobar
                {
                    public function __construct(Foobar $bar)
                    {
                    }
                }
                EOT
        ,
            <<<'EOT'
                <?php

                use stdClass as Foobar;

                class Foobar
                {
                    /**
                     * @var Foobar
                     */
                    private $bar;

                    public function __construct(Foobar $bar)
                    {
                        $this->bar = $bar;
                    }
                }
                EOT

        ];

        yield  'Aliased relative import' => [
            <<<'EOT'
                <?php

                use stdClass as Foobar;

                class Foobar
                {
                    public function __construct(Foobar\Bar $bar)
                    {
                    }
                }
                EOT
        ,
            <<<'EOT'
                <?php

                use stdClass as Foobar;

                class Foobar
                {
                    /**
                     * @var Foobar\Bar
                     */
                    private $bar;

                    public function __construct(Foobar\Bar $bar)
                    {
                        $this->bar = $bar;
                    }
                }
                EOT

        ];

        yield  'Ignores promoted properties' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function __construct(Foobar $foo, private Barfoo $bar)
                    {
                    }
                }
                EOT
        ,
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @var Foobar
                     */
                    private $foo;

                    public function __construct(Foobar $foo, private Barfoo $bar)
                    {
                        $this->foo = $foo;
                    }
                }
                EOT

        ];

        yield 'Importing property before constants' => [
            <<<'EOT'
                <?php
                class Foobar
                {
                    const FOO = 1;

                    public function __construct(string $bar)
                    {
                    }
                }
                EOT
        ,
            <<<'EOT'
                <?php
                class Foobar
                {
                    const FOO = 1;
                    /**
                     * @var string
                     */
                    private $bar;

                    public function __construct(string $bar)
                    {
                        $this->bar = $bar;
                    }
                }
                EOT

        ];
    }

    /**
     * @dataProvider provideCompleteConstructorPromote
     */
    public function testCompleteConstructorPromote(string $example, string $expected): void
    {
        $source = SourceCode::fromString($example);
        $transformer = new CompleteConstructor($this->reflectorForWorkspace($example), $this->updater(), 'private', true);
        $transformed = wait($transformer->transform($source));
        $this->assertEquals((string) $expected, (string) $transformed->apply($source));
    }

    /**
     * @return Generator<string,array{string,string}>
     */
    public function provideCompleteConstructorPromote(): Generator
    {
        yield  'It does adds assignations and properties' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function __construct($foo, $bar)
                    {
                    }
                }
                EOT
        ,
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function __construct(private $foo, private $bar)
                    {
                    }
                }
                EOT

        ];

        yield  'It does adds assignations and properties with types' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function __construct(string $foo, string $bar)
                    {
                    }
                }
                EOT
        ,
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function __construct(private string $foo, private string $bar)
                    {
                    }
                }
                EOT

        ];
    }

    /**
     * @dataProvider provideCompleteConstructorWithParentClass
     */
    public function testCompleteConstructorWithParentClass(string $example, string $expected): void
    {
        $source = SourceCode::fromString($example);
        $transformer = new CompleteConstructor($this->reflectorForWorkspace($example), $this->updater(), 'private', true);
        $transformed = wait($transformer->transform($source));
        $this->assertEquals((string) $expected, (string) $transformed->apply($source));
    }

    /**
    * @return Generator<array{string, string}>
    */
    public function provideCompleteConstructorWithParentClass(): Generator
    {
        yield 'Do not promote constructor arguments if a parent class already has the same argument' => [
            <<<'EOT'
                <?php
                class A
                {
                    public function __construct(public string $someString) {}
                }


                class SomeClassTest extends A
                {
                    public function __construct(public int $test, string $someString)
                    {
                        parent::__construct($someString);
                    }
                }
                EOT,
            <<<'EOT'
                <?php
                class A
                {
                    public function __construct(public string $someString) {}
                }


                class SomeClassTest extends A
                {
                    public function __construct(public int $test, string $someString)
                    {
                        parent::__construct($someString);
                    }
                }
                EOT,
        ];

        yield 'No promotion if there is a parent class constructor somewhere in the hierarchy' => [
            <<<'EOT'
                class B {
                    public function __construct(private string $a) {}
                }
                class A extends B {}

                class Foo extends A {
                    public function __construct(string $a) {parent::__construct($a);}
                }
                EOT,
            <<<'EOT'
                class B {
                    public function __construct(private string $a) {}
                }
                class A extends B {}

                class Foo extends A {
                    public function __construct(string $a) {parent::__construct($a);}
                }
                EOT,
            ];

    }

}
