<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\Container;
use PhpBench\DependencyInjection\ExtensionInterface;
use Phpactor\Filesystem\Adapter\Composer\ComposerFileListProvider;
use Phpactor\Filesystem\Adapter\Git\GitFilesystem;
use Phpactor\Filesystem\Adapter\Simple\SimpleFilesystem;
use Phpactor\Filesystem\Domain\ChainFileListProvider;
use Phpactor\Filesystem\Domain\Cwd;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\Filesystem\Domain\MappedFilesystemRegistry;

class SourceCodeFilesystemExtension implements ExtensionInterface
{
    const FILESYSTEM_GIT = 'git';
    const FILESYSTEM_COMPOSER = 'composer';
    const FILESYSTEM_SIMPLE = 'simple';

    public function getDefaultConfig()
    {
        return [];
    }

    public function load(Container $container)
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
            foreach ($container->get('composer.class_loaders') as $classLoader) {
                $providers[] = new ComposerFileListProvider($cwd, $classLoader);
            }
            return new SimpleFilesystem($cwd, new ChainFileListProvider($providers));
        }, [ 'source_code_filesystem.filesystem' => [ 'name' => self::FILESYSTEM_COMPOSER ]]);

        $container->register('source_code_filesystem.registry', function (Container $container) {
            $filesystems = [];
            foreach ($container->getServiceIdsForTag('source_code_filesystem.filesystem') as $serviceId => $attributes) {
                $filesystems[$attributes['name']] = $container->get($serviceId);
            }

            return new MappedFilesystemRegistry($filesystems);
        });
    }
}
