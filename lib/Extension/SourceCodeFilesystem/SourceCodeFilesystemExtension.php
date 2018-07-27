<?php

namespace Phpactor\Extension\SourceCodeFilesystem;

use Phpactor\Filesystem\Adapter\Composer\ComposerFileListProvider;
use Phpactor\Filesystem\Adapter\Git\GitFilesystem;
use Phpactor\Filesystem\Adapter\Simple\SimpleFilesystem;
use Phpactor\Filesystem\Domain\ChainFileListProvider;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Filesystem\Domain\MappedFilesystemRegistry;
use Phpactor\Filesystem\Domain\Exception\NotSupported;
use Phpactor\Filesystem\Domain\FallbackFilesystemRegistry;
use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Container;
use Phpactor\MapResolver\Resolver;
use Phpactor\Extension\SourceCodeFilesystem\Command\ClassSearchCommand;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilestem\Application\ClassSearch;

class SourceCodeFilesystemExtension implements Extension
{
    const FILESYSTEM_GIT = 'git';
    const FILESYSTEM_COMPOSER = 'composer';
    const FILESYSTEM_SIMPLE = 'simple';

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema)
    {
    }

    public function load(ContainerBuilder $container)
    {
        $this->registerFilesystems($container);
        $this->registerCommands($container);
        $this->registerApplicationServices($container);
    }

    private function registerFilesystems(ContainerBuilder $container)
    {
        $container->register('source_code_filesystem.git', function (Container $container) {
            return new GitFilesystem(FilePath::fromString($container->getParameter('cwd')));
        }, [ 'source_code_filesystem.filesystem' => [ 'name' => self::FILESYSTEM_GIT ]]);
        
        $container->register('source_code_filesystem.simple', function (Container $container) {
            return new SimpleFilesystem(FilePath::fromString($container->getParameter('cwd')));
        }, [ 'source_code_filesystem.filesystem' => ['name' => self::FILESYSTEM_SIMPLE]]);
        
        $container->register('source_code_filesystem.composer', function (Container $container) {
            $providers = [];
            $cwd = FilePath::fromString($container->getParameter('cwd'));
            $classLoaders = $container->get('composer.class_loaders');
        
            if (!$classLoaders) {
                throw new NotSupported('No composer class loaders found/configured');
            }
        
            foreach ($classLoaders as $classLoader) {
                $providers[] = new ComposerFileListProvider($cwd, $classLoader);
            }
        
            return new SimpleFilesystem($cwd, new ChainFileListProvider($providers));
        }, [ 'source_code_filesystem.filesystem' => [ 'name' => self::FILESYSTEM_COMPOSER ]]);
        
        $container->register('source_code_filesystem.registry', function (Container $container) {
            $filesystems = [];
            foreach ($container->getServiceIdsForTag('source_code_filesystem.filesystem') as $serviceId => $attributes) {
                try {
                    $filesystems[$attributes['name']] = $container->get($serviceId);
                } catch (NotSupported $exception) {
                    $container->get('monolog.logger')->warning(sprintf(
                        'Filesystem "%s" not supported: "%s"',
                        $attributes['name'],
                        $exception->getMessage()
                    ));
                }
            }
        
            return new FallbackFilesystemRegistry(
                new MappedFilesystemRegistry($filesystems),
                'simple'
            );
        });
    }

    private function registerCommands(ContainerBuilder $container)
    {
        $container->register('command.class_search', function (Container $container) {
            return new ClassSearchCommand(
                $container->get('application.class_search'),
                $container->get('console.dumper_registry')
            );
        }, [ 'ui.console.command' => []]);
    }

    private function registerApplicationServices(ContainerBuilder $container)
    {
        $container->register('application.class_search', function (Container $container) {
            return new ClassSearch(
                $container->get('source_code_filesystem.registry'),
                $container->get('class_to_file.converter'),
                $container->get('reflection.reflector')
            );
        });
    }
}
