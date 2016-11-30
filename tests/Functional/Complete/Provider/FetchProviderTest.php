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
        $provider->provide($scope, $suggestions);

        $this->assertEquals($expected, $suggestions->all());
    }

    public function provideProvider()
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
        $this->fooba█
    }
}
EOT
                , [ 'foobar' ],
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
}
