<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use PHPUnit\Framework\Attributes\DataProvider;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseNamedParameterCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Generator;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseNamedParameterCompletorTest extends TolerantCompletorTestCase
{
    #[DataProvider('provideComplete')]
    public function testComplete(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    public static function provideComplete(): Generator
    {
        yield 'Variable' => [
            '<?php $<>', []
        ];

        yield 'Constructor' => [
            '<?php class A{function __construct(string $one){}} new A(o<>',
            [
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => 'one: ',
                    'short_description' => 'string $one',
                ]
            ]
        ];

        yield 'Method' => [
            '<?php class A{function bee(string $one){}} $a = new A(); $a->bee(o<>',
            [
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => 'one: ',
                    'short_description' => 'string $one',
                ]
            ]
        ];
        yield 'no completion after string literal' => [
            '<?php class A{function bee(string $one){}} $a = new A(); $a->bee(\'foo\'<>',
            [
            ]
        ];

        yield 'Ignore when completing a variable' => [
            '<?php class A{function bee(string $one){}} $a = new A(); $a->bee($o<>',
            [
            ]
        ];

        yield 'Method call in partial method call' => [
            '<?php class B {function boo(): B{}}' .
            'class A{function bee(string $one){}} $b=new B();$a=new A(); $a->bee($b->boo()-><>',
            [
            ]
        ];

        yield 'Method call in method call' => [
            '<?php class B {function boo(string $two): B{}}' .
            'class A{function bee(string $one){}} $b=new B();$a=new A(); $a->bee($b->boo(<>',
            [
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => 'two: ',
                    'short_description' => 'string $two',
                ]
            ]
        ];

        yield 'Static' => [
            '<?php class A{static function bee(string $one){}} A::bee(o<>',
            [
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => 'one: ',
                    'short_description' => 'string $one',
                ]
            ]
        ];

        yield 'Static call begin' => [
            '<?php class A{static function bee(string $one){}} A::bee(<>',
            [
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => 'one: ',
                    'short_description' => 'string $one',
                ]
            ]
        ];
        yield 'function' => [
            '<?php function bee(string $one){} bee(<>',
            [
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => 'one: ',
                    'short_description' => 'string $one',
                ]
            ]
        ];

        yield 'Attributes' => [
            <<<PHP
                <?php
                #[Attribute]
                class SomeAttribute {
                    public function __construct(private string \$param) {}
                }

                #[SomeAttribute(<>)]
                class Foo {}
                PHP,
            [
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => 'param: ',
                    'short_description' => 'string $param',
                ]
            ]
        ];
    }

    #[DataProvider('provideCouldNotComplete')]
    public function testCouldNotComplete(string $source): void
    {
        $this->assertCouldNotComplete($source);
    }

    /**
     * @return Generator<string, array{string}>
     */
    public static function provideCouldNotComplete(): Generator
    {
        yield 'empty string' => [ '<?php  <>' ];
        yield 'function call' => [ '<?php echo<>' ];
        yield 'variable with space' => [ '<?php $foo <>' ];
        yield 'static variable' => ['<?php Foobar::$<>'];
    }

    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        $reflector = ReflectorBuilder::create()->addSource($source)->build();
        return new WorseNamedParameterCompletor($reflector, $this->formatter());
    }
}
