<?php

namespace Phpactor\Extension\ExtensionManager;

use Composer\Composer;
use Composer\DependencyResolver\Pool;
use Composer\Factory;
use Composer\IO\BufferIO;
use Composer\IO\ConsoleIO;
use Composer\Installer;
use Composer\Json\JsonFile;
use Composer\Package\Version\VersionSelector;
use Composer\Repository\ArrayRepository;
use Composer\Repository\ComposerRepository;
use Composer\Repository\CompositeRepository;
use Composer\Repository\InstalledFilesystemRepository;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\ExtensionManager\Adapter\Composer\ComposerExtensionConfigLoader;
use Phpactor\Extension\ExtensionManager\Adapter\Composer\ComposerExtensionRepository;
use Phpactor\Extension\ExtensionManager\Adapter\Composer\PackageExtensionFactory;
use Phpactor\Extension\ExtensionManager\Adapter\Console\SymfonyProgressLogger;
use Phpactor\Extension\ExtensionManager\Model\DependentExtensionFinder;
use Phpactor\Extension\ExtensionManager\Adapter\Composer\ComposerVersionFinder;
use Phpactor\Extension\ExtensionManager\Adapter\Composer\LazyComposerInstaller;
use Phpactor\Extension\ExtensionManager\Command\InstallCommand;
use Phpactor\Extension\ExtensionManager\Command\ListCommand;
use Phpactor\Extension\ExtensionManager\Command\RemoveCommand;
use Phpactor\Extension\ExtensionManager\Command\UpdateCommand;
use Phpactor\Extension\ExtensionManager\EventSubscriber\PostInstallSubscriber;
use Phpactor\Extension\ExtensionManager\Model\ExtensionConfig;
use Phpactor\Extension\ExtensionManager\Model\ExtensionFileGenerator;
use Phpactor\Extension\ExtensionManager\Rpc\ExtensionInstallHandler;
use Phpactor\Extension\ExtensionManager\Rpc\ExtensionListHandler;
use Phpactor\Extension\ExtensionManager\Rpc\ExtensionRemoveHandler;
use Phpactor\Extension\ExtensionManager\Service\ExtensionLister;
use Phpactor\Extension\ExtensionManager\Service\InstallerService;
use Phpactor\Extension\ExtensionManager\Service\RemoverService;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
use Phpactor\MapResolver\Resolver;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Webmozart\PathUtil\Path;

class ExtensionManagerExtension implements Extension
{
    const PARAM_EXTENSION_REPOSITORY_FILE = 'extension_manager.extension_dirname';
    const PARAM_INSTALLED_EXTENSIONS_FILE = 'extension_manager.extension_list_path';
    const PARAM_EXTENSION_VENDOR_DIR = 'extension_manager.extension_vendor_dir';
    const PARAM_ROOT_PACKAGE_NAME = 'extension_manager.root_package_name';
    const PARAM_EXTENSION_CONFIG_FILE = 'extension_manager.config_path';
    const PARAM_VENDOR_DIR = 'extension_manager.vendor_dir';
    const PARAM_MINIMUM_STABILITY = 'extension_manager.minimum_stability';
    const PARAM_REPOSITORIES = 'extension_manager.repositories';
    const PARAM_QUIET = 'extension_manager.quiet';

    public function configure(Resolver $resolver): void
    {
        $resolver->setRequired([
            self::PARAM_EXTENSION_VENDOR_DIR,
            self::PARAM_VENDOR_DIR,
            self::PARAM_EXTENSION_CONFIG_FILE,
            self::PARAM_INSTALLED_EXTENSIONS_FILE,
        ]);

        $resolver->setDefaults([
            self::PARAM_EXTENSION_VENDOR_DIR => '%application_root%/extensions',
            self::PARAM_VENDOR_DIR => '%application_root%/vendor',
            self::PARAM_EXTENSION_CONFIG_FILE => '%application_root%/extensions.json',
            self::PARAM_INSTALLED_EXTENSIONS_FILE => '%application_root%/extensions/extensions.php',

            self::PARAM_ROOT_PACKAGE_NAME => 'phpactor-extensions',
            self::PARAM_MINIMUM_STABILITY => 'stable',
            self::PARAM_REPOSITORIES => [],
            self::PARAM_QUIET => false,
        ]);
    }

    public function load(ContainerBuilder $container): void
    {
        $this->registerCommands($container);
        $this->registerComposer($container);
        $this->registerModel($container);
        $this->registerService($container);

        if (class_exists(RpcExtension::class)) {
            $this->registerRpc($container);
        }
    }

    private function registerCommands(ContainerBuilder $container): void
    {
        $container->register('extension_manager.command.install-extension', function (Container $container) {
            return new InstallCommand(
                $container->get('extension_manager.service.installer')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'extension:install' ] ]);

        $container->register('extension_manager.command.list', function (Container $container) {
            return new ListCommand($container->get('extension_manager.service.lister'));
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'extension:list' ] ]);

        $container->register('extension_manager.command.update', function (Container $container) {
            return new UpdateCommand($container->get('extension_manager.service.installer'));
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'extension:update' ] ]);

        $container->register('extension_manager.command.remove', function (Container $container) {
            return new RemoveCommand(
                $container->get('extension_manager.service.remover')
            );
        }, [ ConsoleExtension::TAG_COMMAND => [ 'name' => 'extension:remove' ] ]);
    }

    private function registerComposer(ContainerBuilder $container): void
    {
        $container->register('extension_manager.composer', function (Container $container) {
            $this->initializeComposer($container);
            return $this->createComposer($container);
        });

        $container->register('extension_manager.composer_for_installer', function (Container $container) {
            // after modifying the config file, it is necessary to re-initialize composer in order
            // that it takes notice of the modifications. instead of doing this we simply provide the
            // installer with a new instance of composer (rather than the original instance which we need
            // to instantiate earlier).
            return $this->createComposer($container);
        });

        $container->register('extension_manager.installer', function (Container $container) {
            $installer = Installer::create(
                $container->get('extension_manager.io'),
                $container->get('extension_manager.composer_for_installer')
            );
            $installer->setAdditionalInstalledRepository($container->get('extension_manager.repository.primary'));

            return $installer;
        });

        $container->register('extension_manager.io', function (Container $container) {
            $helperSet  = new HelperSet([
                'question' => new QuestionHelper(),
            ]);

            if ($this->isRpcCommand($container) || $container->getParameter(self::PARAM_QUIET)) {
                return new BufferIO();
            }

            return new ConsoleIO(
                $container->get(ConsoleExtension::SERVICE_INPUT),
                $container->get(ConsoleExtension::SERVICE_OUTPUT),
                $helperSet
            );
        });

        $container->register('extension_manager.repository.primary', function (Container $container) {
            return new InstalledFilesystemRepository(new JsonFile($this->repositoryFile($container)));
        });

        $container->register('extension_manager.repository.combined', function (Container $container) {
            return new CompositeRepository([
                $container->get('extension_manager.repository.primary'),
                new InstalledFilesystemRepository(new JsonFile($this->extensionRepositoryFile($container)))
            ]);
        });

        $container->register('extension_manager.repository.pool', function (Container $container) {
            $pool = new Pool('dev');

            $repositoryManager = $container->get('extension_manager.composer')->getRepositoryManager();

            foreach ($repositoryManager->getRepositories() as $repository) {
                $pool->addRepository($repository);
            }

            return $pool;
        });

        $container->register('extension_manager.repository.packagist', function (Container $container) {
            $repositoryManager = $container->get('extension_manager.composer')->getRepositoryManager();

            foreach ($repositoryManager->getRepositories() as $repository) {
                if ($repository instanceof ComposerRepository) {
                    return $repository;
                }
            }

            return new ArrayRepository();
        });

        $container->register('extension_manager.composer.version_selector', function (Container $container) {
            return new VersionSelector($container->get('extension_manager.repository.pool'));
        });
    }

    private function repositoryFile(Container $container)
    {
        return Path::join([
            $this->resolvePath($container, self::PARAM_VENDOR_DIR),
            'composer',
            'installed.json'
        ]);
    }

    private function extensionRepositoryFile(Container $container)
    {
        return Path::join([
            $this->resolvePath($container, self::PARAM_EXTENSION_VENDOR_DIR),
            'composer',
            'installed.json'
        ]);
    }

    private function registerModel(ContainerBuilder $container): void
    {
        $container->register('extension_manager.adapter.extension_config_loader', function (Container $container) {
            return new ComposerExtensionConfigLoader(
                $this->resolvePath($container, self::PARAM_EXTENSION_CONFIG_FILE),
                $container->getParameter(self::PARAM_ROOT_PACKAGE_NAME),
                $this->resolvePath($container, self::PARAM_EXTENSION_VENDOR_DIR),
                $container->getParameter(self::PARAM_MINIMUM_STABILITY),
                $container->getParameter(self::PARAM_REPOSITORIES)
            );
        });
        $container->register('extension_manager.adapter.composer.version_finder', function (Container $container) {
            return new ComposerVersionFinder(
                $container->get('extension_manager.composer.version_selector'),
                $container->getParameter(self::PARAM_MINIMUM_STABILITY)
            );
        });
        $container->register('extension_manager.model.installer', function (Container $container) {
            return new LazyComposerInstaller($container);
        });
        $container->register('extension_manager.model.package_extension_factory', function (Container $container) {
            return new PackageExtensionFactory();
        });
        $container->register('extension_manager.model.dependency_finder', function (Container $container) {
            return new DependentExtensionFinder($container->get('extension_manager.model.extension_repository'));
        });
        $container->register('extension_manager.model.extension_repository', function (Container $container) {
            return new ComposerExtensionRepository(
                $container->get('extension_manager.repository.combined'),
                $container->get('extension_manager.repository.primary'),
                $container->get('extension_manager.repository.packagist'),
                $container->get('extension_manager.model.package_extension_factory')
            );
        });
    }

    private function createComposer(Container $container): Composer
    {
        $composer = Factory::create(
            $container->get('extension_manager.io'),
            $this->resolvePath($container, self::PARAM_EXTENSION_CONFIG_FILE)
        );

        $composer->getEventDispatcher()->addSubscriber(new PostInstallSubscriber(
            new ExtensionFileGenerator(
                $this->resolvePath($container, self::PARAM_INSTALLED_EXTENSIONS_FILE)
            ),
            $container->get('extension_manager.model.package_extension_factory')
        ));
        return $composer;
    }

    private function registerService(ContainerBuilder $container): void
    {
        $container->register('extension_manager.service.progress', function (Container $container) {
            $output = $this->isRpcCommand($container) || $container->getParameter(self::PARAM_QUIET)
                ? new BufferedOutput()
                : $container->get(ConsoleExtension::SERVICE_OUTPUT);
            return new SymfonyProgressLogger($output);
        });

        $container->register('extension_manager.service.installer', function (Container $container) {
            return new InstallerService(
                $container->get('extension_manager.model.installer'),
                $container->get('extension_manager.adapter.extension_config_loader'),
                $container->get('extension_manager.adapter.composer.version_finder'),
                $container->get('extension_manager.model.extension_repository'),
                $container->get('extension_manager.service.progress')
            );
        });

        $container->register('extension_manager.service.lister', function (Container $container) {
            return new ExtensionLister(
                $container->get('extension_manager.model.extension_repository')
            );
        });

        $container->register('extension_manager.service.remover', function (Container $container) {
            return new RemoverService(
                $container->get('extension_manager.model.installer'),
                $container->get('extension_manager.model.dependency_finder'),
                $container->get('extension_manager.model.extension_repository'),
                $container->get('extension_manager.adapter.extension_config_loader')
            );
        });
    }

    private function initializeComposer(Container $container): void
    {
        $path = $this->resolvePath($container, self::PARAM_EXTENSION_CONFIG_FILE);

        // create the directory if it doesn't already exist
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        /** @var ExtensionConfig $config */
        $config = $container->get('extension_manager.adapter.extension_config_loader')->load();

        // unconditionally write the configuration file (updates any
        // configuration parameters which may have been set).
        $config->write();
    }

    private function registerRpc(ContainerBuilder $container): void
    {
        $container->register('extension_manager.rpc.handler.list', function (Container $container) {
            return new ExtensionListHandler($container->get('extension_manager.model.extension_repository'));
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ExtensionListHandler::NAME]]);

        $container->register('extension_manager.rpc.handler.install', function (Container $container) {
            return new ExtensionInstallHandler($container->get('extension_manager.service.installer'));
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ExtensionInstallHandler::NAME]]);

        $container->register('extension_manager.rpc.handler.remove', function (Container $container) {
            return new ExtensionRemoveHandler($container->get('extension_manager.service.remover'));
        }, [ RpcExtension::TAG_RPC_HANDLER => ['name' => ExtensionRemoveHandler::NAME]]);
    }

    private function resolvePath(Container $container, string $path)
    {
        return $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve($container->getParameter($path));
    }

    private function isRpcCommand(Container $container): bool
    {
        /** @var InputInterface $input */
        $input = $container->get(ConsoleExtension::SERVICE_INPUT);
        $cmd = $input->getFirstArgument();

        return $cmd === 'rpc';
    }
}
