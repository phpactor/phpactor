<?php

namespace Phpactor\Tests\System\Extension\Completion\Application;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Phpactor\Extension\CompletionExtra\Application\Complete;
use Phpactor\TestUtils\ExtractOffset;
use Phpactor\Tests\System\SystemTestCase;

class CompleteTest extends SystemTestCase
{
    use ArraySubsetAsserts;

    /**
     * @dataProvider provideComplete
     */
    public function testComplete(string $source, array $expected): void
    {
        $suggestions = $this->complete($source)['suggestions'];
        usort($suggestions, function ($one, $two) {
            return $one['name'] <=> $two['name'];
        });

        if (!$expected) {
            $this->assertEmpty($suggestions);
        }

        foreach ($expected as $index => $expectedSuggestion) {
            $this->assertArraySubset($expectedSuggestion, $suggestions[$index]);
        }
    }
    /**
     * @return array<string,array<int,mixed>>
     */
    public function provideComplete(): array
    {
        return [
            'Public property' => [
                <<<'EOT'
                    <?php

                    class Foobar
                    {
                        public $foo;
                    }

                    $foobar = new Foobar();
                    $foobar-><>

                    EOT
        , [
                    [
                        'type' => 'property',
                        'name' => 'foo',
                        'short_description' => 'pub $foo',
                    ]
                ]
            ],
            'Private property' => [
                <<<'EOT'
                    <?php

                    class Foobar
                    {
                        private $foo;
                    }

                    $foobar = new Foobar();
                    $foobar-><>

                    EOT
        ,
            [ ]
            ],
            'Public property access' => [
                <<<'EOT'
                    <?php

                    class Barar
                    {
                        public $bar;
                    }

                    class Foobar
                    {
                        /**
                         * @var Barar
                         */
                        public $foo;
                    }

                    $foobar = new Foobar();
                    $foobar->foo-><>

                    EOT
               , [
                    [
                        'type' => 'property',
                        'name' => 'bar',
                        'short_description' => 'pub $bar',
                    ]
                ]
            ],
            'Public method with parameters' => [
                <<<'EOT'
                    <?php

                    class Foobar
                    {
                        public function foo(string $zzzbar = 'bar', $def): Barbar
                        {
                        }
                    }

                    $foobar = new Foobar();
                    $foobar-><>

                    EOT
                , [
                    [
                        'type' => 'method',
                        'name' => 'foo',
                        'short_description' => 'pub foo(string $zzzbar = \'bar\', $def): Barbar',
                    ]
                ]
            ],
            'Public method multiple return types' => [
                <<<'EOT'
                    <?php

                    class Foobar
                    {
                        /**
                         * @return Foobar|Barbar
                         */
                        public function foo()
                        {
                        }
                    }

                    $foobar = new Foobar();
                    $foobar-><>

                    EOT
                , [
                    [
                        'type' => 'method',
                        'name' => 'foo',
                        'short_description' => 'pub foo(): Foobar|Barbar',
                    ]
                ]
            ],
            'Private method' => [
                <<<'EOT'
                    <?php

                    class Foobar
                    {
                        private function foo(): Barbar
                        {
                        }
                    }

                    $foobar = new Foobar();
                    $foobar-><>

                    EOT
                , [
                ]
            ],
            'Static property' => [
                <<<'EOT'
                    <?php

                    class Foobar
                    {
                        public static $foo;
                    }

                    $foobar = new Foobar();
                    $foobar::<>

                    EOT
                , [
                    [
                        'type' => 'property',
                        'name' => '$foo',
                        'short_description' => 'pub static $foo',
                    ],
                    [
                        'type' => 'constant',
                        'name' => 'class',
                        'short_description' => 'Foobar',
                    ],
                ]
            ],
            'Static property with previous arrow accessor' => [
                <<<'EOT'
                    <?php

                    class Foobar
                    {
                        public static $foo;

                        /**
                         * @var Foobar
                         */
                        public $me;
                    }

                    $foobar = new Foobar();
                    $foobar->me::<>

                    EOT
                , [
                    [
                        'type' => 'property',
                        'name' => '$foo',
                        'short_description' => 'pub static $foo',
                    ],
                    [
                        'type' => 'constant',
                        'name' => 'class',
                        'short_description' => 'Foobar',
                    ],
                ]
            ],
            'Partially completed' => [
                <<<'EOT'
                    <?php

                    class Foobar
                    {
                        public static $foobar;
                        public static $barfoo;
                    }

                    $foobar = new Foobar();
                    $foobar::f<>

                    EOT
                , [
                    [
                        'type' => 'property',
                        'name' => 'foobar',
                        'short_description' => 'pub static $foobar',
                    ]
                ]
            ],
            'Partially completed' => [
                <<<'EOT'
                    <?php

                    class Foobar
                    {
                        const FOOBAR = 'foobar';
                        const BARFOO = 'barfoo';
                    }

                    $foobar = new Foobar();
                    $foobar::<>

                    EOT
                , [
                    [
                        'type' => 'constant',
                        'name' => 'BARFOO',
                        'short_description' => 'BARFOO = "barfoo"',
                    ],
                    [
                        'type' => 'constant',
                        'name' => 'FOOBAR',
                        'short_description' => 'FOOBAR = "foobar"',
                    ],
                ],
            ],
            'Accessor on new line' => [
                <<<'EOT'
                    <?php

                    class Foobar
                    {
                        public $foobar;
                    }

                    $foobar = new Foobar();
                    $foobar
                        -><>

                    EOT
                , [
                    [
                        'type' => 'property',
                        'name' => 'foobar',
                        'short_description' => 'pub $foobar',
                    ],
                ],
            ]
        ];
    }
    /**
     * @return array{suggestions:array<array<string, mixed>>,issues:array}
     */
    private function complete(string $source): array
    {
        [$source, $offset] = ExtractOffset::fromSource($source);
        $complete = $this->container()->get('application.complete');
        assert($complete instanceof Complete);
        $result = $complete->complete($source, $offset);

        return $result;
    }
}
