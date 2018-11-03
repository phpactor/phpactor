<?php

namespace Phpactor\Extension\SourceCodeFilesystemExtra;

use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
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
use Phpactor\Extension\SourceCodeFilesystemExtra\Command\ClassSearchCommand;
use Phpactor\Extension\SourceCodeFilesystemExtra\SourceCodeFilestem\Application\ClassSearch;

class SourceCodeFilesystemExtraExtension implements Extension
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
        $this->registerCommands($container);
        $this->registerApplicationServices($container);
    }

    private function registerCommands(ContainerBuilder $container)
    {
        $container->register('command.class_search', function (Container $container) {
            return new ClassSearchCommand(
                $container->get('application.class_search'),
                $container->get('console.dumper_registry')
            );
        }, [ 'ui.console.command' => [ 'name' => 'class:search' ]]);
    }

    private function registerApplicationServices(ContainerBuilder $container)
    {
        $container->register('application.class_search', function (Container $container) {
            return new ClassSearch(
                $container->get('source_code_filesystem.registry'),
                $container->get('class_to_file.converter'),
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR)
            );
        });
    }
}
