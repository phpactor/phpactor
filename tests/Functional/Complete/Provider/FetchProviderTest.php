<?php

namespace Phpactor\Tests\Functional\Complete\Provider;

use Phpactor\Tests\Functional\ContainerTestCase;
use Phpactor\Complete\Provider\FetchProvider;
use Phpactor\Complete\Suggestions;

class FetchProviderTest extends ContainerTestCase
{
    /**
     * @dataProvider provideProvider
     */
    public function testProvider($source, $expected)
    {
        $source = '<?php' . PHP_EOL .
            'namespace Phpactor\\Tests\\Functional\\Example; ' . PHP_EOL .
            $source;

        $offset = strpos($source, '█') - 1;
        $source = str_replace('█', '', $source);

        $container = $this->getContainer([
            'source' => $source
        ]);

        $provider = $container->get('completer.provider.property_fetch');
        $scope = $container->get('completer.scope_factory')->create($source, $offset);

        $suggestions = new Suggestions();

        $this->assertTrue($provider->canProvideFor($scope));

        $provider->provide($scope, $suggestions);

        $array = [];
        foreach ($suggestions as $suggestion) {
            $array[] = (string) $suggestion;
        }

        $this->assertEquals($expected, $array);
    }

    public function provideProvider()
    {
        return [
            'it should provide local methods' => [
                <<<'EOT'
class Foobar
{
    public function getFoobar()
    {
        $this->getFoo█
    }
}
EOT
                , [ 'getFoobar' ],
            ],
            'it should provide inherited properties' => [
                <<<'EOT'
class Foobar extends ClassOne
{
    public function getFoobar()
    {
        $this->clas█
    }
}
EOT
                , [ 'classTwo', 'getFoobar', 'getClassTwo' ],
            ],
            'it should provide inherited methods' => [
                <<<'EOT'
class Foobar extends ClassOne
{
    public function getFoobar()
    {
        $this->classTwo->getClassThree()->classOne->class
    }
}
EOT
                , [ 'getFoobar', 'getClassTwo' ],
            ],

            'it should provide local private properties' => [
                <<<'EOT'
class Foobar
{
    private $foobar;

    public function getBarfoo()
    {
        $this->fooba█
    }
}
EOT
                , [ 'foobar' ],
            ],
            'it should provide local protected properties' => [
                <<<'EOT'
class Foobar
{
    protected $foobar;

    public function getBarfoo()
    {
        $this->fooba█
    }
}
EOT
                , [ 'foobar' ],
            ],
            'it should provide local public properties' => [
                <<<'EOT'
class Foobar
{
    public $foobar;

    public function getBarfoo()
    {
        $this->fooba█
    }
}
EOT
                ,[ 'foobar' ],
            ],
            'it should NOT provide private inherited properties' => [
                <<<'EOT'
class Foobar extends ClassOne
{
    public function getBarfoo()
    {
        $this->private█
    }
}
EOT
                , [ ],
            ],
            'it should provide properties on member property object' => [
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
        $bar->classTwo->class█
    }
}
EOT
                , [ 'classThree', 'getClassThree' ],
            ],
        ];
    }
}
