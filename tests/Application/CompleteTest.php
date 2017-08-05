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
            'Public method' => [
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
                    ]
                ]
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
