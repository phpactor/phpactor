<?php

namespace Phpactor\ConfigLoader;

use Phpactor\ConfigLoader\Adapter\Deserializer\JsonDeserializer;
use Phpactor\ConfigLoader\Adapter\Deserializer\YamlDeserializer;
use Phpactor\ConfigLoader\Adapter\PathCandidate\AbsolutePathCandidate;
use Phpactor\ConfigLoader\Adapter\PathCandidate\XdgPathCandidate;
use Phpactor\ConfigLoader\Core\ConfigLoader;
use Phpactor\ConfigLoader\Core\Deserializer;
use Phpactor\ConfigLoader\Core\Deserializers;
use Phpactor\ConfigLoader\Core\PathCandidate;
use Phpactor\ConfigLoader\Core\PathCandidates;
use XdgBaseDir\Xdg;

class ConfigLoaderBuilder
{
    /**
     * @var Deserializer[]
     */
    private array $serializers = [];

    /**
     * @var PathCandidate[]
     */
    private array $candidates = [];

    public static function create(): self
    {
        return new self();
    }

    public function enableJsonDeserializer(string $name): self
    {
        $this->serializers[$name] = new JsonDeserializer();
        return $this;
    }

    public function enableYamlDeserializer(string $name): self
    {
        $this->serializers[$name] = new YamlDeserializer();
        return $this;
    }

    public function addXdgCandidate(string $appName, string $name, string $loader): self
    {
        $this->candidates[] = new XdgPathCandidate($appName, $name, $loader, new Xdg());
        return $this;
    }

    public function addCandidate(string $absolutePath, string $loader): self
    {
        $this->candidates[] = new AbsolutePathCandidate($absolutePath, $loader);
        return $this;
    }

    public function loader(): ConfigLoader
    {
        return new ConfigLoader(
            new Deserializers($this->serializers),
            new PathCandidates($this->candidates)
        );
    }
}
