<?php

namespace Phpactor\ConfigLoader\Tests\Unit\Core;

use Phpactor\ConfigLoader\Adapter\PathCandidate\AbsolutePathCandidate;
use Phpactor\ConfigLoader\Core\ConfigLoader;
use Phpactor\ConfigLoader\Core\Deserializer;
use Phpactor\ConfigLoader\Core\Deserializers;
use Phpactor\ConfigLoader\Core\PathCandidates;
use Phpactor\ConfigLoader\Tests\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ConfigLoaderTest extends TestCase
{
    use ProphecyTrait;
    
    /**
     * @var ObjectProphecy<Deserializer>
     */
    private ObjectProphecy $deserializer;

    public function setUp(): void
    {
        parent::setUp();
        $this->deserializer = $this->prophesize(Deserializer::class);
    }

    public function testDoesNothingWhenEmpty(): void
    {
        $loader = new ConfigLoader(new Deserializers([]), new PathCandidates([]));
        $config = $loader->load();
        $this->assertEquals([], $config);
    }

    public function testIgnoresNotExistingConfigs(): void
    {
        $loader = new ConfigLoader(new Deserializers([]), new PathCandidates([
            new AbsolutePathCandidate($this->workspace->path('foobar'), 'nope')
        ]));
        $config = $loader->load();
        $this->assertEquals([], $config);
    }

    public function testLoadsConfig(): void
    {
        $configFile = $this->workspace->path('foobar.test');
        file_put_contents($configFile, 'test');

        $loader = new ConfigLoader(
            new Deserializers([
                'test' => $this->deserializer->reveal()
            ]),
            new PathCandidates([
                new AbsolutePathCandidate(
                    $configFile,
                    'test'
                )
            ])
        );

        $this->deserializer->deserialize('test')->willReturn([
            'one' => 'two'
        ]);


        $config = $loader->load();
        $this->assertEquals([
            'one' => 'two',
        ], $config);
    }

    public function testMergesConfigsFromFirstToLast(): void
    {
        $loader = $this->createTwoFileLoader();

        $this->deserializer->deserialize('test1')->willReturn([
            'one' => 'two',
            'two' => 'three',
        ]);

        $this->deserializer->deserialize('test2')->willReturn([
            'one' => 'four',
            'two' => 'three',
        ]);

        $config = $loader->load();
        $this->assertEquals([
            'one' => 'four',
            'two' => 'three',
        ], $config);
    }

    public function testMergesNestedKeys(): void
    {
        $loader = $this->createTwoFileLoader();

        $this->deserializer->deserialize('test1')->willReturn([
            'one' => [
                'two' => 'three',
            ],
        ]);

        $this->deserializer->deserialize('test2')->willReturn([
            'one' => [
                'two' => 'three',
                'three' => 'four',
            ],
        ]);

        $config = $loader->load();
        $this->assertEquals([
            'one' => [
                'two' => 'three',
                'three' => 'four',
            ],
        ], $config);
    }

    private function createTwoFileLoader(): ConfigLoader
    {
        $configFile1 = $this->workspace->path('foobar.test');
        $configFile2 = $this->workspace->path('barfoo.test');
        file_put_contents($configFile1, 'test1');
        file_put_contents($configFile2, 'test2');
        
        $loader = new ConfigLoader(
            new Deserializers([
                'test' => $this->deserializer->reveal()
            ]),
            new PathCandidates([
                new AbsolutePathCandidate(
                    $configFile1,
                    'test'
                ),
                new AbsolutePathCandidate(
                    $configFile2,
                    'test'
                ),
            ])
        );
        return $loader;
    }
}
