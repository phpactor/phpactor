<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Generator;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseLocalVariableCompletor;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseLocalVariableCompletorTest extends TolerantCompletorTestCase
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
        yield 'empty string' => [ '<?php  <>' ];
        yield 'function call' => [ '<?php echo<>' ];
        yield 'variable with space' => [ '<?php $foo <>' ];
        yield 'static variable' => ['<?php Foobar::$<>'];
    }

    public function provideComplete(): Generator
    {
        yield 'Nothing' => [
            '<?php $<>', []
        ];

        yield 'Variable' => [
            '<?php $foobar = "hello"; $<>',
            [
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$foobar',
                    'short_description' => '"hello"',
                ]
            ]
        ];

        yield 'Partial variable' => [
            '<?php $barfoo = "goodbye"; $foobar = "hello"; $foo<>',
            [
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$foobar',
                    'short_description' => '"hello"',
                ]
            ]
        ];

        yield 'Variables' => [
            '<?php $barfoo = 12; $foobar = "hello"; $<>',
            [
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$barfoo',
                    'short_description' => '12',
                ],
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$foobar',
                    'short_description' => '"hello"',
                ],
            ]
        ];

        yield 'Complete previously declared variable which had no type' => [
            <<<'EOT'
                <?php

                $callMe = foobar();

                /** @var Barfoo $callMe */
                $callMe = foobar();

                $call<>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$callMe',
                    'short_description' => 'Barfoo',
                ],
            ],
        ];

        yield 'Does not assign offer suggestion for incomplete assignment' => [
            <<<'EOT'
                <?php

                $std = new \stdClass();
                $std = $st<>

                EOT
            , [
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$std',
                    'short_description' => 'stdClass',
                ],
            ],
        ];

        yield 'array keys' => [
            '<?php /** @var array{foo:string,baz:int} */$foo; $fo<>',
            [
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$foo',
                    'short_description' => 'array{foo:string,baz:int}',
                ],
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => "\$foo['baz']",
                    'short_description' => 'int',
                ],
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => "\$foo['foo']",
                    'short_description' => 'string',
                ],
            ]
        ];

        yield 'no array keys' => [
            '<?php /** @var array{string,int} */$foo; $fo<>',
            [
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$foo',
                    'short_description' => 'array{string,int}',
                ],
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => '$foo[0]',
                    'short_description' => 'string',
                ],
                [
                    'type' => Suggestion::TYPE_FIELD,
                    'name' => '$foo[1]',
                    'short_description' => 'int',
                ],
            ]
        ];
    }

    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        $reflector = ReflectorBuilder::create()->addSource($source)->build();
        return new WorseLocalVariableCompletor($reflector, $this->formatter());
    }
}
