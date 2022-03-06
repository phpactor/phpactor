<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseFunctionCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\ReflectorBuilder;
use Generator;

class WorseFunctionCompletorTest extends TolerantCompletorTestCase
{

    /**
     * @dataProvider provideComplete
     */
    public function testComplete(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    /**
     * @dataProvider provideCouldNotComplete
     */
    public function testCouldNotComplete(string $source): void
    {
        $this->assertCouldNotComplete($source);
    }

    public function provideComplete(): Generator
    {
        yield 'function with parameters' => [
            '<?php function mystrpos ($haystack, $needle, $offset = 0):int {}; mystrpos<>', [
                [
                    'type' => Suggestion::TYPE_FUNCTION,
                    'name' => 'mystrpos',
                    'snippet' => 'mystrpos(${1:\$haystack}, ${2:\$needle})${0}',
                ]
            ]
        ];

        yield 'namespaced function name' => [
            '<?php namespace foobar; function barfoo ():int {}; bar<> }', [
                [
                    'type' => Suggestion::TYPE_FUNCTION,
                    'name' => 'barfoo',
                    'short_description' => 'foobar\barfoo(): int',
                    'snippet' => 'barfoo()',
                ]
            ]
        ];
    }

    public function provideCouldNotComplete(): Generator
    {
        yield 'non member access' => [ '<?php $hello<>' ];

        yield 'return value' => [ '<?php function barfoo() {}; class Hello { function barbar(): bar<>' ];

        yield 'parameter type' => [ '<?php function barfoo() {}; class Hello { function barbar(bar<>)' ];
    }
    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        $reflector = ReflectorBuilder::create()->addSource($source)->build();

        return new WorseFunctionCompletor(
            $reflector,
            $this->formatter(),
            $this->snippetFormatter($reflector)
        );
    }
}
