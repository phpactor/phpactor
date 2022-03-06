<?php

namespace Phpactor\Extension\ExtensionManager\Adapter\Composer;

use Phpactor\Extension\ExtensionManager\Model\ExtensionConfig;
use Phpactor\Extension\ExtensionManager\Model\ExtensionConfigLoader;

class ComposerExtensionConfigLoader implements ExtensionConfigLoader
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $rootPackageName;

    /**
     * @var string
     */
    private $vendorDir;

    /**
     * @var string|null
     */
    private $minimumStability;

    /**
     * @var array
     */
    private $repositories;

    public function __construct(
        string $path,
        string $rootPackageName,
        string $vendorDir,
        string $minimumStability = null,
        array $repositories = []
    ) {
        $this->path = $path;
        $this->rootPackageName = $rootPackageName;
        $this->vendorDir = $vendorDir;
        $this->minimumStability = $minimumStability;
        $this->repositories = $repositories;
    }

    public function load(): ExtensionConfig
    {
        return new ComposerExtensionConfig(
            $this->path,
            $this->rootPackageName,
            $this->vendorDir,
            $this->minimumStability,
            $this->repositories
        );
    }
}
