<?php

namespace Phpactor\Tests\System;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;
use Phpactor\Application\Complete;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\Core\SourceCode;
use Phpactor\WorseReflection\ReflectorBuilder;

class CompleteTest extends TestCase
{
    /**
     * @dataProvider provideComplete
     */
    public function testComplete(string $source, int $offset, array $expected)
    {
        $result = $this->complete($source, $offset);

        $this->assertEquals($expected, $result['suggestions']);
        $this->assertEquals(json_encode($expected, true), json_encode($result['suggestions'], true));
    }

    public function provideComplete()
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
$foobar->

EOT
        , 75, [
                    [
                        'type' => 'm',
                        'name' => 'foo',
                        'info' => 'pub $foo',
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
$foobar->

EOT
        , 76,
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
$foobar->foo->

EOT
                , 148, [
                    [
                        'type' => 'm',
                        'name' => 'bar',
                        'info' => 'pub $bar',
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
$foobar->

EOT
                , 132, [
                    [
                        'type' => 'f',
                        'name' => 'foo',
                        'info' => 'pub foo(string $zzzbar = \'bar\', $def): Barbar',
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
$foobar->

EOT
                , 141, [
                    [
                        'type' => 'f',
                        'name' => 'foo',
                        'info' => 'pub foo(): Foobar|Barbar',
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
$foobar->

EOT
                , 105, [
                ]
            ],
            'Static method' => [
                <<<'EOT'
<?php

class Foobar
{
    public static $foo;
}

$foobar = new Foobar();
$foobar::

EOT
                , 82, [
                    [
                        'type' => 'm',
                        'name' => 'foo',
                        'info' => 'pub static $foo',
                    ]
                ]
            ],
            'Static method with previous arrow accessor' => [
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
$foobar->me::

EOT
                , 138, [
                    [
                        'type' => 'm',
                        'name' => 'foo',
                        'info' => 'pub static $foo',
                    ],
                    [
                        'type' => 'm',
                        'name' => 'me',
                        'info' => 'pub $me: Foobar',
                    ]
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
$foobar::f

EOT
                , 113, [
                    [
                        'type' => 'm',
                        'name' => 'foobar',
                        'info' => 'pub static $foobar',
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
$foobar::

EOT
                , 116, [
                    [
                        'type' => 'm',
                        'name' => 'FOOBAR',
                        'info' => 'const FOOBAR',
                    ],
                    [
                        'type' => 'm',
                        'name' => 'BARFOO',
                        'info' => 'const BARFOO',
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
    ->

EOT
                , 83, [
                    [
                        'type' => 'm',
                        'name' => 'foobar',
                        'info' => 'pub $foobar',
                    ],
                ],
            ]
        ];
    }

    private function complete(string $source, $offset)
    {
        $reflector = ReflectorBuilder::create()->addSource($source)->build();
        $complete = new Complete($reflector);

        $result = $complete->complete($source, $offset);

        return $result;
    }

    /**
     * @dataProvider provideErrors
     */
    public function testErrors(string $source, int $offset, array $expected)
    {
        $results = $this->complete($source, $offset);
        $this->assertEquals($expected, $results['issues']);
    }

    public function provideErrors()
    {
        return [
            [
                <<<'EOT'
<?php

$asd = 'asd';
$asd->
EOT
                ,27,
                [
                    'Cannot complete members on scalar value (string)',
                ]
            ],
            [
                <<<'EOT'
<?php

$asd->
EOT
                ,13,
                [
                    'Variable "asd" is undefined',
                ]
            ],
            [
                <<<'EOT'
<?php

$asd = new BooBar();
$asd->
EOT
                ,34,
                [
                    'Could not find class "BooBar"',
                ]
            ],
            [
                <<<'EOT'
<?php

class Foobar
{
    public $foobar;
}

$foobar = new Foobar();
$foobar->barbar->;
EOT
                ,86,
                [
                    'Class "Foobar" has no properties named "barbar"',
                ]
            ]
        ];
    }
}
