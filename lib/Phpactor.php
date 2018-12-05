<?php

namespace Phpactor;

use Webmozart\PathUtil\Path;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Core\CoreExtension;
use Phpactor\Extension\CodeTransformExtra\CodeTransformExtension;
use Phpactor\Extension\CompletionExtra\CompletionExtraExtension;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\CompletionRpc\CompletionRpcExtension;
use Phpactor\Extension\CompletionWorse\CompletionWorseExtension;
use Phpactor\Extension\Navigation\NavigationExtension;
use Phpactor\Extension\SourceCodeFilesystemExtra\SourceCodeFilesystemExtraExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflectionExtra\WorseReflectionExtraExtension;
use Phpactor\Extension\ExtensionManager\ExtensionManagerExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\ClassMover\ClassMoverExtension;
use Phpactor\FilePathResolverExtension\FilePathResolverExtension;
use Phpactor\Extension\ContextMenu\ContextMenuExtension;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\Console\ConsoleExtension;
use Phpactor\Extension\ClassToFile\ClassToFileExtension;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\ComposerAutoloader\ComposerAutoloaderExtension;
use Phpactor\MapResolver\Resolver;
use Phpactor\Container\Extension;
use Symfony\Component\Console\Input\InputInterface;
use Phpactor\Extension\ClassToFileExtra\ClassToFileExtraExtension;
use Composer\XdebugHandler\XdebugHandler;
use Phpactor\ConfigLoader\ConfigLoaderBuilder;

class Phpactor
{
    public static function boot(InputInterface $input, string $vendorDir): PhpactorContainer
    {
        $config = [];

        $cwd = getcwd();

        $loader = ConfigLoaderBuilder::create()
            ->enableJsonDeserializer('json')
            ->enableYamlDeserializer('yaml')
            ->addXdgCandidate('phpactor', 'phpactor.json', 'json')
            ->addXdgCandidate('phpactor', 'phpactor.yml', 'yaml')
            ->addCandidate($cwd . '/.phpactor.json', 'json')
            ->addCandidate($cwd . '/.phpactor.yml', 'yaml')
            ->loader();

        $config = $loader->load();
        $config[CoreExtension::COMMAND] = $input->getFirstArgument();
        $config[FilePathResolverExtension::PARAM_APPLICATION_ROOT] = realpath(__DIR__ . '/..');
        $config = self::configureExtensionManager($config, $vendorDir);

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
            ExtensionManagerExtension::class,
        ];

        if (file_exists($config[ExtensionManagerExtension::PARAM_INSTALLED_EXTENSIONS_FILE])) {
            $extensionNames = array_merge($extensionNames, require($config[ExtensionManagerExtension::PARAM_INSTALLED_EXTENSIONS_FILE]));
        }

        $container = new PhpactorContainer();

        $container->register('config_loader.candidates', function () use ($loader) {
            return $loader->candidates();
        });

        $masterSchema = new Resolver();
        $extensions = [];
        foreach ($extensionNames as $extensionClass) {
            $schema = new Resolver();

            if (!class_exists($extensionClass)) {
                echo sprintf('Extension "%s" does not exist', $extensionClass). PHP_EOL;
                continue;
            }

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

    private static function configureExtensionManager(array $config, string $vendorDir): array
    {
        $config[ExtensionManagerExtension::PARAM_EXTENSION_VENDOR_DIR] = $extensionDir = __DIR__ . '/../extensions';
        $config[ExtensionManagerExtension::PARAM_INSTALLED_EXTENSIONS_FILE] = $extensionsFile = $extensionDir. '/extensions.php';
        $config[ExtensionManagerExtension::PARAM_VENDOR_DIR] = $vendorDir;
        $config[ExtensionManagerExtension::PARAM_EXTENSION_CONFIG_FILE] = $extensionDir .'/extensions.json';

        $autoloadFile = $config[ExtensionManagerExtension::PARAM_EXTENSION_VENDOR_DIR] . '/autoload.php';

        if (file_exists($autoloadFile)) {
            require($autoloadFile);
        }

        return $config;
    }
}
