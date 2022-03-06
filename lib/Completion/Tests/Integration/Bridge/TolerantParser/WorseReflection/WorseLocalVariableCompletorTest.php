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
                    'short_description' => 'string',
                ]
            ]
        ];

        yield 'Partial variable' => [
            '<?php $barfoo = "goodbye"; $foobar = "hello"; $foo<>',
            [
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$foobar',
                    'short_description' => 'string',
                ]
            ]
        ];

        yield 'Variables' => [
            '<?php $barfoo = 12; $foobar = "hello"; $<>',
            [
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$barfoo',
                    'short_description' => 'int',
                ],
                [
                    'type' => Suggestion::TYPE_VARIABLE,
                    'name' => '$foobar',
                    'short_description' => 'string',
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
    }
    protected function createTolerantCompletor(TextDocument $source): TolerantCompletor
    {
        $reflector = ReflectorBuilder::create()->addSource($source)->build();
        return new WorseLocalVariableCompletor($reflector, $this->formatter());
    }
}
