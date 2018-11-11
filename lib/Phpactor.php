<?php

namespace Phpactor;

use Webmozart\PathUtil\Path;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Core\CoreExtension;
use Phpactor\Extension\CodeTransform\CodeTransformExtension;
use Phpactor\Extension\CompletionExtra\CompletionExtraExtension;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\CompletionRpc\CompletionRpcExtension;
use Phpactor\Extension\CompletionWorse\CompletionWorseExtension;
use Phpactor\Extension\Navigation\NavigationExtension;
use Phpactor\Extension\SourceCodeFilesystemExtra\SourceCodeFilesystemExtraExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflectionExtra\WorseReflectionExtraExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\ClassMover\ClassMoverExtension;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
use Phpactor\Extension\ContextMenu\ContextMenuExtension;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\Config\ConfigLoader;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\Extension;
use Symfony\Component\Console\Input\InputInterface;
use Phpactor\Config\Paths;
use Phpactor\Extension\ClassToFileExtra\ClassToFileExtraExtension;
use Composer\XdebugHandler\XdebugHandler;
use Phpactor\Extension\LanguageServer\LanguageServerExtension;

class Phpactor
{
    public static function boot(InputInterface $input, string $vendorDir): PhpactorContainer
    {
        $config = [];

        $configLoader = new ConfigLoader();
        $config = $configLoader->loadConfig();
        $config[CoreExtension::VENDOR_DIRECTORY] = $vendorDir;
        $config[CoreExtension::COMMAND] = $input->getFirstArgument();
        $config[FilePathResolverExtension::PARAM_APPLICATION_ROOT] = __DIR__ . '/..';

        $cwd = getcwd();

        if ($input->hasParameterOption([ '--working-dir', '-d' ])) {
            $config[FilePathResolverExtension::PARAM_PROJECT_ROOT] = $cwd = $input->getParameterOption([ '--working-dir', '-d' ]);
        }

        if (!isset($config[CoreExtension::XDEBUG_DISABLE]) || $config[CoreExtension::XDEBUG_DISABLE]) {
            $xdebug = new XdebugHandler('PHPACTOR', '--ansi');
            $xdebug->check();
            unset($xdebug);
        }

        $extensionNames = [
            CoreExtension::class,
            ClassToFileExtraExtension::class,
            ClassToFileExtension::class,
            ClassMoverExtension::class,
            CodeTransformExtension::class,
            CompletionExtraExtension::class,
            CompletionWorseExtension::class,
            CompletionExtension::class,
            CompletionRpcExtension::class,
            NavigationExtension::class,
            ContextMenuExtension::class,
            RpcExtension::class,
            SourceCodeFilesystemExtraExtension::class,
            SourceCodeFilesystemExtension::class,
            WorseReflectionExtension::class,
            WorseReflectionExtraExtension::class,
            FilePathResolverExtension::class,
            LoggingExtension::class,
            ComposerAutoloaderExtension::class,
            ConsoleExtension::class,
            LanguageServerExtension::class
        ];

        $container = new PhpactorContainer();

        $paths = new Paths(null, $cwd);
        $container->register('config.paths', function () use ($paths) {
            return $paths;
        });

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
