<?php

namespace Phpactor\ConfigLoader\Tests\Integration;

use Phpactor\ConfigLoader\ConfigLoaderBuilder;
use Phpactor\ConfigLoader\Tests\TestCase;

class ConfigLoaderTest extends TestCase
{
    public function testLoadsConfigurationInOrderDeclared(): void
    {
        $path1 = $this->createConfig('one.json', [ 'one' => [ 'two' => 'three' ] ]);
        $path2 = $this->createConfig('two.json', [ 'one' => [ 'two' => 'four' ] ]);

        $config = ConfigLoaderBuilder::create()
            ->enableJsonDeserializer('json')
            ->addCandidate($path1, 'json')
            ->addCandidate($path2, 'json')
            ->loader()->load();

        $this->assertEquals([
            'one' => [
                'two' => 'four'
            ]
        ], $config);
    }

    private function createConfig(string $string, array $array): string
    {
        $path = $this->workspace->path($string);
        file_put_contents($path, json_encode($array));

        return $path;
    }
}
