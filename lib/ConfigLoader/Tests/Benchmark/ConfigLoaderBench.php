<?php

namespace Phpactor\ConfigLoader\Tests\Benchmark;

use Phpactor\ConfigLoader\Adapter\Deserializer\JsonDeserializer;
use Phpactor\ConfigLoader\Adapter\Deserializer\YamlDeserializer;
use Phpactor\ConfigLoader\Adapter\PathCandidate\AbsolutePathCandidate;
use Phpactor\ConfigLoader\ConfigLoaderBuilder;
use Phpactor\ConfigLoader\Core\ConfigLoader;
use Phpactor\ConfigLoader\Core\Deserializers;
use Phpactor\ConfigLoader\Core\PathCandidates;
use Phpactor\ConfigLoader\Tests\TestCase;

/**
 * @BeforeMethods({"setUp"})
 * @Iterations(30)
 * @Revs(50)
 * @OutputTimeUnit("milliseconds")
 */
class ConfigLoaderBench extends TestCase
{
    public function __construct()
    {
        parent::__construct(static::class);
    }
    private string $config1;

    private string $config2;

    private string $config1yaml;

    private string $config2yaml;

    public function setUp(): void
    {
        parent::setUp();
        $this->config1 = $this->workspace->path('config1.json');
        $this->config2 = $this->workspace->path('config2.json');
        $this->config1yaml = $this->workspace->path('config1.yaml');
        $this->config2yaml = $this->workspace->path('config2.yaml');
        file_put_contents($this->config1, json_encode(['one' => 'two']));
        file_put_contents($this->config2, json_encode(['two' => 'three']));
        file_put_contents($this->config1yaml, 'one: two');
        file_put_contents($this->config2yaml, 'two: three');
    }

    public function benchJsonLoadConfig(): void
    {
        $loader = new ConfigLoader(
            new Deserializers([
                'json' => new JsonDeserializer(),
            ]),
            new PathCandidates([
                new AbsolutePathCandidate($this->config1, 'json'),
                new AbsolutePathCandidate($this->config2, 'json'),
            ])
        );
        $loader->load();
    }

    public function benchJsonLoadConfigWithBuilder(): void
    {
        ConfigLoaderBuilder::create()
            ->enableJsonDeserializer('json')
            ->addCandidate($this->config1, 'json')
            ->addCandidate($this->config2, 'json')
            ->loader()->load();
    }

    public function benchJsonLoadConfigWithNonExistingYaml(): void
    {
        $loader = new ConfigLoader(
            new Deserializers([
                'json' => new JsonDeserializer(),
                'yaml' => new YamlDeserializer(),
            ]),
            new PathCandidates([
                new AbsolutePathCandidate($this->config1, 'json'),
                new AbsolutePathCandidate('/path/to/yaml1', 'yaml'),
                new AbsolutePathCandidate('/path/to/yaml2', 'yaml'),
                new AbsolutePathCandidate($this->config2, 'json'),
            ])
        );
        $loader->load();
    }

    public function benchJsonPlainPhp(): void
    {
        $config = array_merge(
            json_decode(file_get_contents($this->config1), true),
            json_decode(file_get_contents($this->config2), true)
        );
    }

    public function benchYamlLoadConfig(): void
    {
        $loader = new ConfigLoader(
            new Deserializers([
                'yaml' => new YamlDeserializer(),
            ]),
            new PathCandidates([
                new AbsolutePathCandidate($this->config1yaml, 'yaml'),
                new AbsolutePathCandidate($this->config2yaml, 'yaml'),
            ])
        );
        $loader->load();
    }
}
