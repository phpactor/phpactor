<?php

namespace Phpactor;

use Phpactor\Extension\LanguageServer\LanguageServerExtension;
use Webmozart\PathUtil\Path;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Core\CoreExtension;
use Phpactor\Extension\CodeTransform\CodeTransformExtension;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\Navigation\NavigationExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\ClassMover\ClassMoverExtension;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Config\ConfigLoader;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\Extension;
use Symfony\Component\Console\Input\InputInterface;
use Phpactor\Config\Paths;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Composer\XdebugHandler\XdebugHandler;

class Phpactor
{
    public static function boot(InputInterface $input, string $vendorDir): PhpactorContainer
    {
        $config = [];

        $configLoader = new ConfigLoader();
        $config = $configLoader->loadConfig();
        $config[CoreExtension::VENDOR_DIRECTORY] = $vendorDir;

        $cwd = getcwd();
        if ($input->hasParameterOption([ '--working-dir', '-d' ])) {
            $config[CoreExtension::WORKING_DIRECTORY] = $cwd = $input->getParameterOption([ '--working-dir', '-d' ]);
        }
        $config['command'] = $input->getFirstArgument();

        if (!isset($config[CoreExtension::XDEBUG_DISABLE]) || $config[CoreExtension::XDEBUG_DISABLE]) {
            $xdebug = new XdebugHandler('PHPACTOR', '--ansi');
            $xdebug->check();
            unset($xdebug);
        }

        $extensionNames = [
            CoreExtension::class,
            ClassToFileExtension::class,
            ClassMoverExtension::class,
            CodeTransformExtension::class,
            CompletionExtension::class,
            NavigationExtension::class,
            RpcExtension::class,
            SourceCodeFilesystemExtension::class,
            WorseReflectionExtension::class,
            LanguageServerExtension::class,
        ];

        $container = new PhpactorContainer();

        $paths = new Paths(null, $cwd);
        $container->register('config.paths', function () use ($paths) {
            return $paths;
        });

        if (false === isset($config[CoreExtension::CACHE_DIR])) {
            $config[CoreExtension::CACHE_DIR] = $paths->userData('cache');
        }

        // > method resolve config
        $masterSchema = new Resolver();
        $extensions = [];
        foreach ($extensionNames as $extensionClass) {
            $schema = new Resolver();
            $extension = new $extensionClass();
            if (!$extension instanceof Extension) {
                throw new RuntimeException(sprintf(
                    'Phpactor extension "%s" must implement interface "%s"',
                    get_class($extension),
                    Extension::class
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
