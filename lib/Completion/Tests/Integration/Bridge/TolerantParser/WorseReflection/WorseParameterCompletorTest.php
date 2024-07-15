<?php

namespace Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\WorseReflection;

use Generator;
use Phpactor\Completion\Bridge\TolerantParser\TolerantCompletor;
use Phpactor\Completion\Bridge\TolerantParser\WorseReflection\WorseParameterCompletor;
use Phpactor\Completion\Core\Suggestion;
use Phpactor\Completion\Tests\Integration\Bridge\TolerantParser\TolerantCompletorTestCase;
use Phpactor\TextDocument\TextDocument;
use Phpactor\WorseReflection\ReflectorBuilder;

class WorseParameterCompletorTest extends TolerantCompletorTestCase
{
    /**
     * @dataProvider provideCompleteMethodParameter
     */
    public function testCompleteMethodParameter(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    public function provideCompleteMethodParameter(): Generator
    {
        yield 'no parameters' => [
            <<<'EOT'
                <?php
                class Foobar { public function barbar() {} }

                $foobar = new Foobar();
                $foobar->barbar($<>
                EOT
            , [
            ]
        ];

        yield 'parameter' => [
            <<<'EOT'
                <?php
                class Foobar { public function barbar(string $foo) {} }

                $param = 'string';
                $foobar = new Foobar();
                $foobar->barbar($<>
                EOT
            , [
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$param',
                    'short_description' => '"string" => param #1 string $foo',
                ]
            ]
        ];

        yield 'parameter, 2nd pos' => [
            <<<'EOT'
                <?php
                class Foobar { public function barbar(string $foo, Foobar $bar) {} }

                $param = 'string';
                $foobar = new Foobar();
                $foobar->barbar($foo, $<>
                EOT
            , [
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$foobar',
                    'short_description' => 'Foobar => param #2 Foobar $bar',
                ]
            ]
        ];

        yield 'parameter, 3rd pos' => [
            <<<'EOT'
                <?php
                class Foobar { public function barbar(string $foo, Foobar $bar, $mixed) {} }

                $param = 'string';
                $foobar = new Foobar();
                $foobar->barbar($param, $foobar, $<>);
                EOT
            , [
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$foobar',
                    'short_description' => 'Foobar => param #3 $mixed',
                ],
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$param',
                    'short_description' => '"string" => param #3 $mixed',
                ],
            ]
        ];

        yield 'no suggestions when exceeding parameter arity' => [
            <<<'EOT'
                <?php
                class Foobar { public function barbar(string $foo) {} }

                $param = 'string';
                $foobar = new Foobar();
                $foobar->barbar($param, $<>);
                EOT
            , []
        ];

        yield 'function parameter completion' => [
            <<<'EOT'
                <?php
                function foobar($bar, string $barbar) {}

                $hello = 'string';
                foobar($param, $<>);
                EOT
            ,[
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$hello',
                    'short_description' => '"string" => param #2 string $barbar',
                ],
            ],
        ];

        yield 'function parameter completion, single parameters' => [
            <<<'EOT'
                <?php
                function foobar($bar, string $barbar) {}

                $hello = 'string';
                foobar($<>);
                EOT
            ,[
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$hello',
                    'short_description' => '"string" => param #1 $bar',
                ],
            ],
        ];


        yield 'does not use variables declared after offset a' => [
            <<<'EOT'
                <?php
                function foobar($bar, string $barbar) {}

                class Hello
                {
                    public functoin goodbye()
                    {
                        $hello = 'string';
                        foobar($<>
                        $hello = 1234;
                    }
                }
                EOT
            ,[
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$hello',
                    'short_description' => '"string" => param #1 $bar',
                ],
            ],
        ];

        yield 'does not use variables declared after offset with bracket' => [
            <<<'EOT'
                <?php
                function foobar($bar, string $barbar) {}

                class Hello
                {
                    public function goodbye()
                    {
                        $hello = 'string';
                        foobar(<>
                        $hello = 1234;
                    }
                }
                EOT
            ,[
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$hello',
                    'short_description' => '"string" => param #1 $bar',
                ],
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$this',
                    'short_description' => 'Hello => param #1 $bar',
                ],
            ],
        ];

        yield 'can complete methods declared after the offset' => [
            <<<'EOT'
                <?php
                class Hello
                {
                    public function goodbye()
                    {
                        $this->bonjour($<>
                    }

                    public function bonjour($bar)
                    {
                    }
                }
                EOT
            ,[
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$this',
                    'short_description' => 'Hello => param #1 $bar',
                ],
            ],
        ];

        yield 'complete on open braclet' => [
            <<<'EOT'
                <?php
                class Hello
                {
                    public function goodbye()
                    {
                        $this->bonjour(<>
                    }

                    public function bonjour($bar)
                    {
                    }
                }
                EOT
            ,[
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$this',
                    'short_description' => 'Hello => param #1 $bar',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideCompleteFunctionParameter
     */
    public function testCompleteFunctionParameter(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    public function provideCompleteFunctionParameter(): Generator
    {
        yield 'complete after comma' => [
            <<<'EOT'
                <?php
                function foobar($bar, string $barbar) {}

                $hello = 'string';
                foobar($hello, <>);
                EOT
            ,[
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$hello',
                    'short_description' => '"string" => param #2 string $barbar',
                ],
            ],
        ];

        yield 'complete on open braclet' => [
            <<<'EOT'
                <?php
                function foobar($bar, string $barbar) {}

                $hello = 'string';
                foobar(<>
                EOT
            ,[
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$hello',
                    'short_description' => '"string" => param #1 $bar',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideCompleteStaticClassParameter
     */
    public function testCompleteStaticClassParameter(string $source, array $expected): void
    {
        $this->assertComplete($source, $expected);
    }

    public function provideCompleteStaticClassParameter(): Generator
    {
        yield 'complete static method parameter' => [
            <<<'EOT'
                <?php
                class Foobar { public static function barbar(string $foo, Foobar $bar, $mixed) {} }

                $param = 'string';
                Foobar::barbar($param, $foobar, $<>);
                EOT
            ,[
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$param',
                    'short_description' => '"string" => param #3 $mixed',
                ],
            ],
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
        yield 'non member access' => [ '<?php $hello<>' ];
        yield 'variable with previous accessor' => [ '<?php $foobar->hello; $hello<>' ];
        yield 'statement with previous member access' => [ '<?php if ($foobar && $this->foobar) { echo<>' ];
        yield 'variable with previous static member access' => [ '<?php Hello::hello(); $foo<>' ];
    }

    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        $reflector = ReflectorBuilder::create()->addSource($source)->build();
        return new WorseParameterCompletor($reflector, $this->formatter());
    }
}
