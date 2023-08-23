<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\Helper\VariableCompletionHelper;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseSubscriptCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Generator;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseLocalVariableCompletor;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseSubscriptCompletorTest extends TolerantCompletorTestCase
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

    /**
     * @return Generator<string,array{string}>
     */
    public function provideCouldNotComplete(): Generator
    {
        yield 'empty string' => [ '<?php  <>' ];
        yield 'function call' => [ '<?php echo<>' ];
        yield 'variable with space' => [ '<?php $foo <>' ];
        yield 'static variable' => ['<?php Foobar::$<>'];
    }

    /**
     * @return Generator<array{string,array<int,array<string,string>>}>
     */
    public function provideComplete(): Generator
    {
        yield 'variable' => [
            '<?php $foo<>', []
        ];

        yield 'subscript with no type' => [
            '<?php $foo[<>', []
        ];
        yield 'subscript with type' => [
            '<?php /** @var array{foo:string} $foo */$foo[<>', [
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => '[\'foo\']',
                    'short_description' => 'string',
                ]
            ]
        ];
        yield 'nested subscript with type' => [
            '<?php /** @var array{foo:array{one:int}} $foo */$foo[\'foo\'][<>', [
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => '[\'one\']',
                    'short_description' => 'int',
                ]
            ]
        ];
    }


    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        $reflector = ReflectorBuilder::create()->addSource($source)->build();
        return new WorseSubscriptCompletor($reflector);
    }
}
