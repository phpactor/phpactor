<?php

namespace Phpactor\Extension\ExtensionManager\Adapter\Composer;

use Phpactor\Extension\ExtensionManager\Model\ExtensionConfig;
use RuntimeException;

class ComposerExtensionConfig implements ExtensionConfig
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string|null
     */
    private $minimumStability;

    /**
     * @var array
     */
    private $repositories;

    /**
     * @var string
     */
    private $rootPackageName;

    /**
     * @var string
     */
    private $vendorDir;

    /**
     * @var string
     */
    private $originalContents;

    public function __construct(
        string $path,
        string $rootPackageName,
        string $vendorDir,
        string $minimumStability = null,
        array $repositories = []
    ) {
        $this->path = $path;
        $this->minimumStability = $minimumStability;
        $this->repositories = $repositories;
        $this->rootPackageName = $rootPackageName;
        $this->vendorDir = $vendorDir;
        $this->originalContents = '{}';
        $this->config = $this->read();
    }

    public function require(string $extension, string $version): void
    {
        if (!isset($this->config['require'])) {
            $this->config['require'] = [];
        }
        
        $this->config['require'][$extension] = $version;
    }

    public function unrequire(string $extension): void
    {
        if (!isset($this->config['require'][$extension])) {
            return;
        }

        unset($this->config['require'][$extension]);

        if (empty($this->config['require'])) {
            unset($this->config['require']);
        }
    }

    public function revert(): void
    {
        file_put_contents($this->path, $this->originalContents);
    }

    public function write(): void
    {
        file_put_contents($this->path, json_encode($this->config, JSON_PRETTY_PRINT));
    }

    private function read(): array
    {
        $config = $this->readFile();
        $config = $this->decodeJson($config);
        $config = $this->configure($config);

        return $config;
    }

    private function configure(array $config): array
    {
        if (!isset($config['config'])) {
            $config['config'] = [];
        }

        $config['config']['name'] = $this->rootPackageName;
        $config['config']['vendor-dir'] = $this->vendorDir;

        if ($this->minimumStability) {
            $config['minimum-stability'] = $this->minimumStability;
        }

        if ($this->repositories) {
            if (!isset($config['repositories'])) {
                $config['repositories'] = [];
            }

            $config['repositories'] = $this->repositories;
        }

        return $config;
    }

    private function decodeJson($contents)
    {
        $config = json_decode($contents, true);
        
        if (null === $config) {
            throw new RuntimeException(sprintf(
                'Invalid JSON file "%s"',
                $this->path
            ));
        }

        return $config;
    }

    private function readFile(): string
    {
        if (!file_exists($this->path)) {
            return '{}';
        }
        
        $contents = (string) file_get_contents($this->path);
        $this->originalContents = $contents;

        return $contents;
    }
}
