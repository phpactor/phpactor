<?php

namespace Phpactor\Tests\Application;

use PHPUnit\Framework\TestCase;
use Phpactor\WorseReflection\Logger\ArrayLogger;
use Phpactor\Application\Complete;
use Phpactor\WorseReflection\SourceCodeLocator\StringSourceLocator;
use Phpactor\WorseReflection\Reflector;
use Phpactor\WorseReflection\SourceCode;

class CompleteTest extends TestCase
{
    /**
     * @dataProvider provideComplete
     */
    public function testComplete(string $source, int $offset, array $expected)
    {
        $this->assertEquals([
            'suggestions' => $expected,
        ], $this->complete($source, $offset));

        $this->assertEquals(json_encode([
            'suggestions' => $expected,
        ], true), json_encode($this->complete($source, $offset), true));
        
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
        $tmpFileName = tempnam(sys_get_temp_dir(), 'phpactor_test');
        file_put_contents($tmpFileName, (string) $source);

        $reflector = Reflector::create(new StringSourceLocator(SourceCode::fromString($source)), $logger);
        $complete = new Complete($reflector);

        $result = $complete->complete($tmpFileName, $offset);
        unlink($tmpFileName);

        return $result;
    }
}
