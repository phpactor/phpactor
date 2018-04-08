<?php

namespace Phpactor;

use XdgBaseDir\Xdg;
use Webmozart\PathUtil\Path;
use Symfony\Component\Yaml\Yaml;
use Phpactor\Application;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Core\CoreExtension;
use Phpactor\Extension\CodeTransform\CodeTransformExtension;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\PathFinder\PathFinderExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\ClassMover\ClassMoverExtension;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Config\ConfigLoader;
use Phpactor\Container\Schema;
use Phpactor\Container\Extension;
use Symfony\Component\Console\Input\InputInterface;
use Phpactor\Config\Paths;

class Phpactor
{
    public static function boot(InputInterface $input): PhpactorContainer
    {
        $config = [];

        $configLoader = new ConfigLoader();
        $config = $configLoader->loadConfig();

        if ($input->hasParameterOption([ '--working-dir', '-d' ])) {
            $config['cwd'] = $input->getParameterOption([ '--working-dir', '-d' ]);
        }

        $extensionNames = [
            CoreExtension::class,
            ClassMoverExtension::class,
            CodeTransformExtension::class,
            CompletionExtension::class,
            PathFinderExtension::class,
            RpcExtension::class,
            SourceCodeFilesystemExtension::class,
            WorseReflectionExtension::class
        ];

        $container = new PhpactorContainer();
        // TODO: Put this in core ext??

        $container->register('config.paths', function () { return new Paths(); });

        // > method resolve config
        $masterSchema = new Schema();
        $extensions = [];
        foreach ($extensionNames as $extensionClass) {
            $schema = new Schema();
            $extension = new $extensionClass();
            if (!$extension instanceof Extension) {
                throw new RuntimeException(sprintf(
                    'Phpactor extension "%s" must implement interface "%s"',
                    get_class($extension), Extension::class
                ));
            }

            $extension->configure($schema);
            $extensions[] = $extension;
            $masterSchema = $masterSchema->merge($schema);
        }
        $config = $masterSchema->resolve($config);

        // > method configure container
        foreach ($extensions as $extension) {
            $extension->load($container);
        }

        return $container->build($config);
    }
    /**
     * If the path is relative we need to use the current working path
     * because otherwise it will be the script path, which is wrong in the
     * context of a PHAR.
     *
     * @deprecated Use webmozart Path instead.
     *
     * @param string $path
     *
     * @return string
     */
    public static function normalizePath(string $path): string
    {
        if (substr($path, 0, 1) == DIRECTORY_SEPARATOR) {
            return $path;
        }

        return getcwd().DIRECTORY_SEPARATOR.$path;
    }

    public static function relativizePath(string $path): string
    {
        if (0 === strpos($path, getcwd())) {
            return substr($path, strlen(getcwd()) + 1);
        }

        return $path;
    }

    public static function isFile(string $string)
    {
        $containsInvalidNamespaceChars = (bool) preg_match('{[\.\*/]}', $string);

        if ($containsInvalidNamespaceChars) {
            return true;
        }

        return file_exists($string);
    }
}
