<?php

namespace Phpactor\Extension\ClassToFile;

use Phpactor\Container\Extension;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\ClassFileConverter\Domain\ClassToFileFileToClass;
use Phpactor\ClassFileConverter\Adapter\Composer\ComposerClassToFile;
use Phpactor\ClassFileConverter\Adapter\Simple\SimpleClassToFile;
use Phpactor\ClassFileConverter\Domain\ChainClassToFile;
use Phpactor\ClassFileConverter\Adapter\Composer\ComposerFileToClass;
use Phpactor\ClassFileConverter\Adapter\Simple\SimpleFileToClass;
use Phpactor\Container\Container;
use Phpactor\ClassFileConverter\Domain\ChainFileToClass;

class ClassToFileExtension implements Extension
{
    const SERVICE_CONVERTER = 'class_to_file.converter';
    const PARAM_CLASS_LOADERS = 'composer.class_loaders';
    const PARAM_PROJECT_ROOT = 'class_to_file.project_root';
    const PARAM_BRUTE_FORCE_CONVERSION = 'class_to_file.brute_force_conversion';


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::PARAM_PROJECT_ROOT => '%project_root%',
            self::PARAM_BRUTE_FORCE_CONVERSION => true,
        ]);
        $schema->setDescriptions([
            self::PARAM_PROJECT_ROOT => 'Root path of the project (e.g. where composer.json is)',
            self::PARAM_BRUTE_FORCE_CONVERSION => 'If composer not found, fallback to scanning all files (very time consuming depending on project size)',
        ]);
    }


    public function load(ContainerBuilder $container): void
    {
        $container->register(self::SERVICE_CONVERTER, function (Container $container) {
            return new ClassToFileFileToClass(
                $container->get('class_to_file.class_to_file'),
                $container->get('class_to_file.file_to_class')
            );
        });

        $container->register('class_to_file.class_to_file', function (Container $container) {
            $classToFiles = [];
            foreach ($container->get(self::PARAM_CLASS_LOADERS) as $classLoader) {
                $classToFiles[] = new ComposerClassToFile($classLoader);
            }

            if ($container->parameter(self::PARAM_BRUTE_FORCE_CONVERSION)->bool() && empty($classToFiles)) {
                $projectDir = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve($container->parameter(self::PARAM_PROJECT_ROOT)->string());
                $classToFiles[] = new SimpleClassToFile($projectDir);
            }

            return new ChainClassToFile($classToFiles);
        });

        $container->register('class_to_file.file_to_class', function (Container $container) {
            $fileToClasses = [];
            foreach ($container->get(ComposerAutoloaderExtension::SERVICE_AUTOLOADERS) as $classLoader) {
                $fileToClasses[] = new ComposerFileToClass($classLoader);
            }

            if (empty($fileToClasses)) {
                $fileToClasses[] = new SimpleFileToClass();
            }

            return new ChainFileToClass($fileToClasses);
        });
    }
}
