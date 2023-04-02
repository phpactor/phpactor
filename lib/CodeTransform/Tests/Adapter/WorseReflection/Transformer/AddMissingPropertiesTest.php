<?php

namespace Phpactor\CodeTransform\Tests\Adapter\WorseReflection\Transformer;

use Generator;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\CodeTransform\Adapter\WorseReflection\Transformer\AddMissingProperties;
use Phpactor\CodeTransform\Tests\Adapter\WorseReflection\WorseTestCase;
use function Amp\Promise\wait;

class AddMissingPropertiesTest extends WorseTestCase
{
    /**
     * @dataProvider provideCompleteConstructor
     */
    public function testAddMissingProperties(string $example, string $expected): void
    {
        $this->workspace()->put('Bag.php', '<?php namespace Test; class Bag { public function bar(): Boo {} }');
        $this->workspace()->put('Boo.php', '<?php namespace Test; class Boo{}');

        $source = SourceCode::fromString($example);
        $transformer = new AddMissingProperties($this->reflectorForWorkspace($example), $this->updater());
        $transformed = wait($transformer->transform(SourceCode::fromString($source)));
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
        yield 'It adds missing properties' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello()
                    {
                        $this->hello = 'Hello';
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
                    private $hello;

                    public function hello()
                    {
                        $this->hello = 'Hello';
                    }
                }
                EOT

        ];

        yield 'It adds missing properties with documented type' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @param array<string,mixed> $bar
                     */
                    public function hello(array $bar)
                    {
                        $this->hello = $bar;
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @var array<string,mixed>
                     */
                    private $hello;

                    /**
                     * @param array<string,mixed> $bar
                     */
                    public function hello(array $bar)
                    {
                        $this->hello = $bar;
                    }
                }
                EOT

        ];

        yield 'It ignores existing properties' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @var string
                     */
                    private $hello;

                    public function hello()
                    {
                        $this->hello = 'Hello';
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
                    private $hello;

                    public function hello()
                    {
                        $this->hello = 'Hello';
                    }
                }
                EOT

        ];

        yield 'It ignores existing properties of a different visibility' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @var string
                     */
                    public $hello;

                    public function hello()
                    {
                        $this->hello = 'Hello';
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
                    public $hello;

                    public function hello()
                    {
                        $this->hello = 'Hello';
                    }
                }
                EOT
        ];

        yield 'It appends new properties' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    /**
                     * @var string
                     */
                    public $hello;

                    public function hello()
                    {
                        $this->foobar = 1234;
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
                    public $hello;

                    /**
                     * @var int
                     */
                    private $foobar;


                    public function hello()
                    {
                        $this->foobar = 1234;
                    }
                }
                EOT
        ];

        yield 'It appends new properties in a namespaced class' => [
            <<<'EOT'
                <?php

                namespace Hello;

                class Foobar
                {
                    /**
                     * @var string
                     */
                    public $hello;

                    public function hello()
                    {
                        $this->foobar = 1234;
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                namespace Hello;

                class Foobar
                {
                    /**
                     * @var string
                     */
                    public $hello;

                    /**
                     * @var int
                     */
                    private $foobar;


                    public function hello()
                    {
                        $this->foobar = 1234;
                    }
                }
                EOT
        ];

        yield 'Properties should only be taken from current class' => [
            <<<'EOT'
                <?php

                namespace Hello;

                class Dodo
                {
                    public function goodbye()
                    {
                        $this->dodo = 'string';
                    }
                }

                class Foobar extends Dodo
                {
                    public function hello()
                    {
                        $this->foobar = 1234;
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                namespace Hello;

                class Dodo
                {
                    /**
                     * @var string
                     */
                    private $dodo;

                    public function goodbye()
                    {
                        $this->dodo = 'string';
                    }
                }

                class Foobar extends Dodo
                {
                    /**
                     * @var int
                     */
                    private $foobar;

                    public function hello()
                    {
                        $this->foobar = 1234;
                    }
                }
                EOT
        ];

        yield 'It adds missing properties using the imported type' => [
            <<<'EOT'
                <?php

                use MyLibrary\Hello;

                class Foobar
                {
                    public function hello()
                    {
                        $this->hello = new Hello();
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                use MyLibrary\Hello;

                class Foobar
                {
                    /**
                     * @var Hello
                     */
                    private $hello;

                    public function hello()
                    {
                        $this->hello = new Hello();
                    }
                }
                EOT

        ];

        yield 'It missing properties with an untyped parameter' => [
            <<<'EOT'
                <?php

                use MyLibrary\Hello;

                class Foobar
                {
                    public function hello($string)
                    {
                        $this->hello = $string;
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                use MyLibrary\Hello;

                class Foobar
                {
                    private $hello;

                    public function hello($string)
                    {
                        $this->hello = $string;
                    }
                }
                EOT

        ];

        yield 'It adds missing trait properties within the Trait' => [
            <<<'EOT'
                <?php

                trait Foobar
                {
                    public function hello()
                    {
                        $this->hello = 'goodbye';
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                trait Foobar
                {
                    /**
                     * @var string
                     */
                    private $hello;

                    public function hello()
                    {
                        $this->hello = 'goodbye';
                    }
                }
                EOT
        ];

        yield 'It adds missing property from call expression' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello()
                    {
                        $this->bar = $this->bar();
                    }

                    public function bar(): string
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
                    private $bar;

                    public function hello()
                    {
                        $this->bar = $this->bar();
                    }

                    public function bar(): string
                    {
                    }
                }
                EOT
        ];

        yield 'It adds missing property from array assignment' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello()
                    {
                        $this->bar['foo'] = $this->bar();
                    }

                    public function bar(): string
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
                     * @var array<string,string>
                     */
                    private $bar = [];

                    public function hello()
                    {
                        $this->bar['foo'] = $this->bar();
                    }

                    public function bar(): string
                    {
                    }
                }
                EOT
        ];

        yield 'It adds missing property from array add' => [
            <<<'EOT'
                <?php

                class Foobar
                {
                    public function hello()
                    {
                        $this->bar[] = $this->bar();
                    }

                    public function bar(): string
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
                     * @var string[]
                     */
                    private $bar = [];

                    public function hello()
                    {
                        $this->bar[] = $this->bar();
                    }

                    public function bar(): string
                    {
                    }
                }
                EOT
        ];

        yield 'It imports classes' => [
            <<<'EOT'
                <?php

                use Test\Bag;

                class Foobar
                {
                    public function hello()
                    {
                        $foo = new Bag();
                        $this->foo = $foo->bar();
                    }
                }
                EOT
            ,
            <<<'EOT'
                <?php

                use Test\Bag;
                use Test\Boo;

                class Foobar
                {
                    /**
                     * @var Boo
                     */
                    private $foo;

                    public function hello()
                    {
                        $foo = new Bag();
                        $this->foo = $foo->bar();
                    }
                }
                EOT

        ];
    }

    /**
     * @dataProvider provideDiagnostics
     */
    public function testDiagnostics(string $example, int $diagnosticsCount): void
    {
        $source = SourceCode::fromString($example);
        $transformer = new AddMissingProperties($this->reflectorForWorkspace($example), $this->updater());
        $diagnostics = wait($transformer->diagnostics($source));
        $this->assertCount($diagnosticsCount, $diagnostics);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideDiagnostics(): Generator
    {
        yield 'empty' => [
            '<?php',
            0
        ];

        yield 'missing properties' => [
            '<?php class A { public function bar() { $this->bar = "foo"; } }',
            1
        ];

        yield 'not missing properties' => [
            '<?php class A { private $bar; public function bar() { $this->bar = "foo"; } }',
            0
        ];

        yield 'ignores property from another class' => [
            <<<'EOT'
                <?php

                namespace Test;

                use Test\Yet\AnotherClass;

                class Foo
                {
                    public function test(AnotherClass $anotherClass): void
                    {
                        assert($anotherClass->doesNotMatter instanceof SecretImplementation);

                        $anotherClass->doesNotMatter = 'test';
                    }
                }
                EOT
            , 0
        ];
    }
}
