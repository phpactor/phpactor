<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseArrayKeyCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Generator;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseLocalVariableCompletor;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseArrayKeyCompletorTest extends TolerantCompletorTestCase
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

    public function provideCouldNotComplete(): Generator
    {
        yield [
            '<?php <>',
        ];
    }

    public function provideComplete(): Generator
    {
        yield 'Nothing' => [
            '<?php $<>', []
        ];

        yield 'keys' => [
            '<?php /** @var array{foo:string,baz:int} $foo */ $foo[<>',
            [
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => "'baz'",
                    'short_description' => 'int',
                ],
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => "'foo'",
                    'short_description' => 'string',
                ],
            ]
        ];

        yield 'no keys' => [
            '<?php /** @var array{string,int} $foo */ $foo[<>',
            [
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => "0",
                    'short_description' => 'string',
                ],
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => "1",
                    'short_description' => 'int',
                ],
            ]
        ];
    }

    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        $reflector = ReflectorBuilder::create()->addSource($source)->build();
        return new WorseArrayKeyCompletor($reflector, $this->formatter());
    }
}
