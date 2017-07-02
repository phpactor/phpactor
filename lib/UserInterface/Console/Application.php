<?php

namespace Phpactor\UserInterface\Console;

use Symfony\Component\Console\Application as SymfonyApplication;
use PhpBench\DependencyInjection\Container;
use Phpactor\Container\CoreExtension;
use XdgBaseDir\Xdg;
use Webmozart\PathUtil\Path;
use Symfony\Component\Yaml\Yaml;

class Application extends SymfonyApplication
{
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct('Phpactor', '0.1');
    }

    public function initialize()
    {
        $config = $this->loadConfig();
        $container = new Container([
            CoreExtension::class,
        ], $this->loadConfig());
        $container->init();

        foreach ($container->getServiceIdsForTag('ui.console.command') as $commandId => $attrs) {
            $this->add($container->get($commandId));
        }
    }

    private function loadConfig(): array
    {
        $xdg = new Xdg();
        $configDirs = $xdg->getConfigDirs();

        $configPaths = array_map(function ($configPath) {
            return Path::join($configPath, '/phpactor/phpactor.yml');
        }, $configDirs);
        $configPaths[] = Path::join(getcwd(), '.phpactor.yml');

        $config = [];
        foreach ($configPaths as $configPath) {
            if (file_exists($configPath)) {
                $config = array_merge(
                    $config,
                    Yaml::parse(file_get_contents($configPath))
                );
            }
        }

        return $config;
    }
}
