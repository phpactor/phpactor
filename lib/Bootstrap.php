<?php

namespace Phpactor;

use Phpactor\Config\ConfigLoader;
use Phpactor\Console\Application;
use Phpactor\Extension\PhpactorContainer;
use Phpactor\Extension\Schema;
use Phpactor\Extension\Extension;
use RuntimeException;
use Phpactor\Config\Paths;
use Symfony\Component\Console\Input\InputInterface;
use Phpactor\Extension\Core\CoreExtension;
use Phpactor\Extension\CodeTransform\CodeTransformExtension;
use Phpactor\Extension\Completion\CompletionExtension;
use Phpactor\Extension\PathFinder\PathFinderExtension;
use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\Extension\ClassMover\ClassMoverExtension;
use Phpactor\Extension\Rpc\RpcExtension;

class Bootstrap
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
}
