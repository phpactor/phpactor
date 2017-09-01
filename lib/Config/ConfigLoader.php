<?php

namespace Phpactor\Config;

use XdgBaseDir\Xdg;
use Webmozart\PathUtil\Path;
use Symfony\Component\Yaml\Yaml;

class ConfigLoader
{
    /**
     * @var Xdg
     */
    private $xdg;

    /**
     * @var string
     */
    private $cwd;

    public function __construct(Xdg $xdg = null, string $cwd = null)
    {
        $this->xdg = $xdg ?: new Xdg();
        $this->cwd = $cwd ?: getcwd();
    }

    public function configFiles(): array
    {
        $configDirs = $this->xdg->getConfigDirs();

        $configPaths = array_map(function ($configPath) {
            return Path::join($configPath, '/phpactor/phpactor.yml');
        }, $configDirs);

        $localConfigPath = Path::join($this->cwd, '.phpactor.yml');
        array_unshift($configPaths, $localConfigPath);

        return $configPaths;
    }

    public function configDirs(): array
    {
        $configDirs = $this->xdg->getConfigDirs();
        array_unshift($configDirs, Path::join(getenv('HOME'), '/.vim'));
        array_unshift($configDirs, Path::join(getenv('HOME'), '/.config/nvim'));
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
