<?php

namespace Phpactor\Extension\SourceCodeFilesystem;

use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
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
use Phpactor\Filesystem\Domain\Filesystem;

class SourceCodeFilesystemExtension implements Extension
{
    const FILESYSTEM_GIT = 'git';
    const FILESYSTEM_COMPOSER = 'composer';
    const FILESYSTEM_SIMPLE = 'simple';
    const SERVICE_REGISTRY = 'source_code_filesystem.registry';
    const SERVICE_FILESYSTEM_GIT = 'source_code_filesystem.git';
    const SERVICE_FILESYSTEM_SIMPLE = 'source_code_filesystem.simple';
    const SERVICE_FILESYSTEM_COMPOSER = 'source_code_filesystem.composer';
    const PARAM_PROJECT_ROOT = 'source_code_filesystem.project_root';


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_PROJECT_ROOT => '%project_root%',
        ]);
    }

    public function load(ContainerBuilder $container): void
    {
        $this->registerFilesystems($container);
    }

    private function registerFilesystems(ContainerBuilder $container): void
    {
        $container->register(self::SERVICE_REGISTRY, function (Container $container) {
            $filesystems = [];
            /** @var array<string, array{name: string}> $fileSystemsByTag */
            $fileSystemsByTag = $container->getServiceIdsForTag('source_code_filesystem.filesystem');
            foreach ($fileSystemsByTag as $serviceId => $attributes) {
                try {
                    /** @var Filesystem $filesystem */
                    $filesystem =$container->get($serviceId);
                    $filesystems[$attributes['name']] = $filesystem;
                } catch (NotSupported $exception) {
                    LoggingExtension::channelLogger($container, 'scf')->warning(sprintf(
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
        $container->register(self::SERVICE_FILESYSTEM_GIT, function (Container $container) {
            return new GitFilesystem(FilePath::fromString($this->projectRoot($container)));
        }, [ 'source_code_filesystem.filesystem' => [ 'name' => self::FILESYSTEM_GIT ]]);

        $container->register(self::SERVICE_FILESYSTEM_SIMPLE, function (Container $container) {
            return new SimpleFilesystem(FilePath::fromString($this->projectRoot($container)));
        }, [ 'source_code_filesystem.filesystem' => ['name' => self::FILESYSTEM_SIMPLE]]);

        $container->register(self::SERVICE_FILESYSTEM_COMPOSER, function (Container $container) {
            $providers = [];
            $cwd = FilePath::fromString($this->projectRoot($container));
            $classLoaders = $container->get(ComposerAutoloaderExtension::SERVICE_AUTOLOADERS);

            if (!$classLoaders) {
                throw new NotSupported('No composer class loaders found/configured');
            }

            foreach ($classLoaders as $classLoader) {
                $providers[] = new ComposerFileListProvider($cwd, $classLoader);
            }

            return new SimpleFilesystem($cwd, new ChainFileListProvider($providers));
        }, [ 'source_code_filesystem.filesystem' => [ 'name' => self::FILESYSTEM_COMPOSER ]]);
    }

    private function projectRoot(Container $container): string
    {
        return $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve($container->getParameter(self::PARAM_PROJECT_ROOT));
    }
}
