<?php

namespace Phpactor\Config;

use XdgBaseDir\Xdg;
use Webmozart\PathUtil\Path;
use Symfony\Component\Yaml\Yaml;

class ConfigLoader
{
    /**
     * @var string
     */
    private $cwd;

    /**
     * @var Paths
     */
    private $paths;

    public function __construct(Paths $paths = null, string $cwd = null)
    {
        $this->paths = $paths ?: new Paths();
        $this->cwd = $cwd ?: getcwd();
    }

    public function loadConfig(): array
    {
        $config = [];
        foreach ($this->paths->configFiles() as $configPath) {
            if (false === file_exists($configPath)) {
                continue;
            }

            $config = array_replace_recursive(
                $config,
                (array) Yaml::parse(file_get_contents($configPath))
            );
        }

        return $config;
    }

}
