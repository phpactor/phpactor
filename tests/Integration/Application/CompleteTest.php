<?php

namespace Phpactor\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Core\Logger\ArrayLogger;
use Phpactor\Application\Complete;
use Phpactor\WorseReflection\Core\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\Core\SourceCode;

class CompleteTest extends TestCase
{
    /**
     * @dataProvider provideComplete
     */
    public function testComplete(string $source, int $offset, array $expected)
    {
        $result = $this->complete($source, $offset);

        $this->assertEquals($expected, $result);
        $this->assertEquals(json_encode($expected, true), json_encode($result, true));
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
        $logger = new ArrayLogger();

        $reflector = Reflector::create(new StringSourceLocator(SourceCode::fromString($source)), $logger);
        $complete = new Complete($reflector);

        $result = $complete->complete($source, $offset);

        return $result;
    }
}
