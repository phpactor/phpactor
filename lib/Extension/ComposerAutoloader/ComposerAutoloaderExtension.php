<?php

namespace Phpactor\Extension\ComposerAutoloader;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\MapResolver\Resolver;
use Composer\Autoload\ClassLoader;
use Phpactor\Extension\ComposerAutoloader\ClassLoaderFactory as PhpactorClassLoader;
use Psr\Log\LoggerInterface;
use RuntimeException;

class ComposerAutoloaderExtension implements Extension
{
    const SERVICE_AUTOLOADERS = 'composer.class_loaders';
    const PARAM_AUTOLOADER_PATH = 'composer.autoloader_path';
    const PARAM_AUTOLOAD_DEREGISTER = 'composer.autoload_deregister';
    const PARAM_COMPOSER_ENABLE = 'composer.enable';
    const PARAM_CLASS_MAPS_ONLY = 'composer.class_maps_only';
    const LOG_CHANNEL = 'COMPOSER';

    
    public function configure(Resolver $resolver): void
    {
        $resolver->setDefaults([
            self::PARAM_COMPOSER_ENABLE => true,
            self::PARAM_AUTOLOADER_PATH => '%project_root%/vendor/autoload.php',
            self::PARAM_AUTOLOAD_DEREGISTER => true,
            self::PARAM_CLASS_MAPS_ONLY => true
        ]);
        $resolver->setDescriptions([
            self::PARAM_COMPOSER_ENABLE => 'Include of the projects autoloader to facilitate class location. Note that when including an autoloader code _may_ be executed. This option may be disabled when using the indexer',
            self::PARAM_CLASS_MAPS_ONLY => 'Register the composer class maps only, do not register the autoloader - RECOMMENDED',
            self::PARAM_AUTOLOADER_PATH => 'Path to project\'s autoloader, can be an array',
            self::PARAM_AUTOLOAD_DEREGISTER=> 'Immediately de-register the autoloader once it has been included (prevent conflicts with Phpactor\'s autoloader). Some platforms may require this to be disabled',
        ]);
    }

    
    public function load(ContainerBuilder $container): void
    {
        $container->register(self::SERVICE_AUTOLOADERS, function (Container $container) {
            if (!$container->getParameter(self::PARAM_COMPOSER_ENABLE)) {
                return [];
            }

            $autoloaderPaths = (array) $container->getParameter(self::PARAM_AUTOLOADER_PATH);
            $autoloaderPaths = array_filter(array_map(function ($path) use ($container) {
                $path = $container->get(
                    FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER
                )->resolve($path);
                if (false === file_exists($path)) {
                    $this->logAutoloaderNotFound($container, $path);
                    return false;
                }

                return $path;
            }, $autoloaderPaths));

            if ($container->getParameter(self::PARAM_CLASS_MAPS_ONLY)) {
                return $this->classMapsOnly(LoggingExtension::channelLogger($container, self::LOG_CHANNEL), $autoloaderPaths);
            }

            $currentAutoloaders = spl_autoload_functions();
            $autoloaders = [];


            foreach ($autoloaderPaths as $autoloaderPath) {
                $autoloader = require $autoloaderPath;

                if (!$autoloader instanceof ClassLoader) {
                    throw new RuntimeException('Autoloader is not an instance of ClassLoader');
                }

                $autoloaders[] = $autoloader;
            }

            if ($currentAutoloaders && $container->getParameter(self::PARAM_AUTOLOAD_DEREGISTER)) {
                $this->deregisterAutoloader($currentAutoloaders);
            }

            return $autoloaders;
        });
    }

    private function logAutoloaderNotFound(Container $container, $autoloaderPath): void
    {
        LoggingExtension::channelLogger($container, self::LOG_CHANNEL)->warning(
            sprintf(
                'Could not find autoloader "%s"',
                $autoloaderPath
            )
        );
    }

    private function deregisterAutoloader(array $currentAutoloaders): void
    {
        $autoloaders = spl_autoload_functions();

        if (!$autoloaders) {
            return;
        }

        foreach ($autoloaders as $autoloadFunction) {
            spl_autoload_unregister($autoloadFunction);
        }
        
        foreach ($currentAutoloaders as $autoloader) {
            spl_autoload_register($autoloader);
        }
    }

    private function classMapsOnly(LoggerInterface $logger, array $autoloaderPaths): array
    {
        return array_map(function (string $autoloadPath) use ($logger): ClassLoader {
            $composerPath = dirname($autoloadPath) . '/composer';
            return (new PhpactorClassLoader($composerPath, $logger))->getLoader();
        }, $autoloaderPaths);
    }
}
