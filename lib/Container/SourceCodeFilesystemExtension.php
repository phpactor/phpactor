<?php

namespace Phpactor\Container;

use Composer\Autoload\ClassLoader;
use PhpBench\DependencyInjection\ExtensionInterface;
use Phpactor\Application\ClassCopy;
use Phpactor\Application\ClassMover as ClassMoverApp;
use Phpactor\Application\ClassReflector;
use Phpactor\Application\ClassSearch;
use Phpactor\Application\FileInfo;
use Phpactor\Application\FileInfoAtOffset;
use Phpactor\Application\Helper\ClassFileNormalizer;
use Phpactor\ClassFileConverter\Adapter\Composer\ComposerClassToFile;
use Phpactor\ClassFileConverter\Adapter\Composer\ComposerFileToClass;
use Phpactor\ClassFileConverter\Domain\ChainClassToFile;
use Phpactor\ClassFileConverter\Domain\ChainFileToClass;
use Phpactor\ClassFileConverter\Domain\ClassToFileFileToClass;
use Phpactor\ClassMover\ClassMover;
use Phpactor\Filesystem\Adapter\Composer\ComposerFileListProvider;
use Phpactor\Filesystem\Adapter\Git\GitFilesystem;
use Phpactor\Filesystem\Adapter\Simple\SimpleFilesystem;
use Phpactor\Filesystem\Domain\ChainFileListProvider;
use Phpactor\Filesystem\Domain\Cwd;
use Phpactor\Filesystem\Domain\FilePath;
use Phpactor\UserInterface\Console\Command\ClassCopyCommand;
use Phpactor\UserInterface\Console\Command\ClassMoveCommand;
use Phpactor\UserInterface\Console\Command\ClassReflectorCommand;
use Phpactor\UserInterface\Console\Command\ClassSearchCommand;
use Phpactor\UserInterface\Console\Command\OffsetInfoCommand;
use Phpactor\UserInterface\Console\Command\FileInfoCommand;
use Phpactor\UserInterface\Console\Dumper\DumperRegistry;
use Phpactor\UserInterface\Console\Dumper\IndentedDumper;
use Phpactor\UserInterface\Console\Dumper\JsonDumper;
use Phpactor\UserInterface\Console\Dumper\TableDumper;
use Phpactor\UserInterface\Console\Prompt\BashPrompt;
use Phpactor\UserInterface\Console\Prompt\ChainPrompt;
use Symfony\Component\Console\Application;
use Phpactor\UserInterface\Console\Command\ConfigDumpCommand;
use PhpBench\DependencyInjection\Container;
use Monolog\Logger;
use Phpactor\Application\Complete;
use Phpactor\UserInterface\Console\Command\CompleteCommand;
use Phpactor\Application\ClassReferences;
use Phpactor\UserInterface\Console\Command\ReferencesClassCommand;
use Phpactor\UserInterface\Console\Command\ReferencesMethodCommand;
use Phpactor\Application\ClassMethodReferences;
use Phpactor\Filesystem\Domain\FilesystemRegistry;

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

            return new FilesystemRegistry($filesystems);
        });
    }
}
