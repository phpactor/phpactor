<?php

namespace Phpactor\Config;

use Webmozart\PathUtil\Path;
use XdgBaseDir\Xdg;

class Paths
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

    public function configFiles()
    {
        $paths = array_map(function ($configPath) {
            return Path::join($configPath, '/phpactor.yml');
        }, $this->configPaths());

        $localConfigPath = Path::join($this->cwd, '.phpactor.yml');
        $paths[] = $localConfigPath;

        return $paths;
    }

    public function configPaths(string $suffix = null): array
    {
        $xdgDirs = array_reverse($this->xdg->getConfigDirs());

        $paths = array_map(function ($path) use ($suffix) {
            $path = Path::join($path, '/phpactor');
            if ($suffix) {
                $path = Path::join($path, $suffix);
            }
            return $path;
        }, $xdgDirs);

        return $paths;
    }

    public function existingConfigPaths(string $subPath): array
    {
        return array_filter($this->configPaths($subPath), function ($path) {
            return file_exists($path);
        });
    }

    public function userData(string $subPath = null): string
    {
        $path = $this->xdg->getHomeDataDir() . '/phpactor';

        if ($subPath) {
            $path = Path::join($path, $subPath);
        }

        return $path;
    }
}
