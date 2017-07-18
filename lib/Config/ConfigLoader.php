<?php

namespace Phpactor\Config;

use Symfony\Component\Yaml\Parser;
use XdgBaseDir\Xdg;
use Webmozart\PathUtil\Path;
use Symfony\Component\Yaml\Yaml;

class ConfigLoader
{
    /**
     * @var Xdg
     */
    private $xdg;

    public function __construct(Xdg $xdg = null)
    {
        $this->xdg = $xdg ?: new Xdg();
    }

    public function configFiles(): array
    {
        $configDirs = $this->xdg->getConfigDirs();

        $configPaths = array_map(function ($configPath) {
            return Path::join($configPath, '/phpactor/phpactor.yml');
        }, $configDirs);

        $localConfigPath = Path::join(getcwd(), '.phpactor.yml');
        array_unshift($configPaths, $localConfigPath);

        return $configPaths;
    }

    public function configDirs(): array
    {
        $configDirs = $this->xdg->getConfigDirs();
        array_unshift($configDirs, Path::join(getcwd(), '/.phpactor'));

        return $configDirs;
    }

    public function loadConfig(): array
    {
        $config = [];
        foreach ($this->configFiles() as $configPath) {
            if (false === file_exists($configPath)) {
                continue;
            }

            $config = array_merge_recursive(
                $config,
                (array) Yaml::parse(file_get_contents($configPath))
            );
        }

        return $config;
    }
}


