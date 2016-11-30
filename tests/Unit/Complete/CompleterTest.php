<?php

namespace Phpactor\Tests\Unit\Complete;

use Phpactor\Reflection\ComposerReflector;
use Phpactor\Complete\Completer;
use BetterReflection\SourceLocator\Type\StringSourceLocator;
use BetterReflection\SourceLocator\Type\SingleFileSourceLocator;
use BetterReflection\SourceLocator\Type\AggregateSourceLocator;
use BetterReflection\SourceLocator\Type\AutoloadSourceLocator;
use BetterReflection\Reflector\ClassReflector;
use Phpactor\Complete\Provider\VariableProvider;
use Phpactor\Complete\Provider\FetchProvider;

class CompleterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideComplete
     */
    public function testComplete($source, $expectedCompletions)
    {
        $source = '<?php ' . PHP_EOL . 'namespace Phpactor\\Tests\\Unit\\Complete\\Example; ' . PHP_EOL . $source;
        $offset = strpos($source, '█') - 1;
        $source = str_replace('█', '', $source);
        $completer = $this->getCompleter($source);
        $suggestions = $completer->complete($source, $offset);

        foreach ($expectedCompletions as $expectedCompletion) {
            $this->assertContains(
                $expectedCompletion,
                $suggestions->all(),
                sprintf('Contains: "%s"', implode('", "', $suggestions->all()))
            );
        }
    }

    public function provideComplete()
    {
        return [
            [
                <<<'EOT'
class Foobar
{
    /**
     * @var ClassOne
     */
    private $foobar;

    public function foobar()
    {
        $thi█
    }
}
EOT
                , [ '$this' ],
            ],
            [
                <<<'EOT'
class Foobar
{
    /**
     * @var ClassOne
     */
    private $foobar;

    public function foobar($foobar, $barfoo)
    {
        $foob█
    }
}
EOT
                , [ '$this', '$foobar', '$barfoo', '$_GET' ],
            ],
            [
                <<<'EOT'
class Foobar
{
    /**
     * @var ClassOne
     */
    private $foobar;

    public function foobar()
    {
        $one = 'one';
        $two = 'two';
        $a█

    }
}
EOT
                , [ '$this', '$one', '$two' ],
            ],
            [
                <<<'EOT'
class Foobar
{
    /**
     * @var ClassOne
     */
    public $foobar;

    public function foobar()
    {
        $bar = $this->foobar;
        $bar->classTwo->bar█

    }
}
EOT
                , [ 'classThree', 'getClassThree' ],
            ],
            [
                <<<'EOT'
class Foobar
{
    /**
     * @var ClassOne
     */
    public $foobar;

    public function getFoobar()
    {
        $this->foo█
    }
}
EOT
                , [ 'foobar', 'getFoobar' ],
            ],
        ];
    }

    private function getCompleter(string $source)
    {
        $sourceLocator = new AggregateSourceLocator([
            new StringSourceLocator($source),
            new AutoloadSourceLocator(),
        ]);
        $reflector = new ClassReflector($sourceLocator);

        return new Completer([
            new VariableProvider($reflector),
            new FetchProvider($reflector)
        ]);
    }
}
