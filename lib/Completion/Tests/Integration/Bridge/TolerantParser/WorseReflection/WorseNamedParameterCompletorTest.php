<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseNamedParameterCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Generator;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseNamedParameterCompletorTest extends TolerantCompletorTestCase
{

    /**
     * @dataProvider provideComplete
     */
    public function testComplete(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    public function provideComplete(): Generator
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
    }

    /**
     * @dataProvider provideCouldNotComplete
     */
    public function testCouldNotComplete(string $source): void
    {
        $this->assertCouldNotComplete($source);
    }

    public function provideCouldNotComplete(): Generator
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
