<?php

namespace Phpactor\Container;

use Phpactor\Config\ConfigLoader;
use Phpactor\Console\Application;
use Phpactor\Extension\PhpactorContainer;
use Phpactor\Extension\Schema;
use Phpactor\Extension\Extension;
use RuntimeException;
use Phpactor\Config\Paths;

class Bootstrap
{
    public function boot(): Application
    {
        $config = [];

        //if ($input->hasParameterOption([ '--working-dir', '-d' ])) {
        //    $config['cwd'] = $input->getParameterOption([ '--working-dir', '-d' ]);
        //}

        $configLoader = new ConfigLoader();
        $config = $configLoader->loadConfig();

        $extensionNames = [
            CoreExtension::class,
            CodeTransformExtension::class,
            CompletionExtension::class,
            PathFinderExtension::class,
            RpcExtension::class,
            SourceCodeFilesystemExtension::class,
            WorseReflectionExtension::class
        ];

        $container = new PhpactorContainer();
        $container->register('config.paths', function () { return new Paths(); });

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

        foreach ($extensions as $extension) {
            $extension->load($container);
        }

        $container = $container->build($config);
        $application = new Application();

        foreach ($container->getServiceIdsForTag('ui.console.command') as $serviceId => $serviceAttrs) {
            $application->add($container->get($serviceId));
        }

        return $application;
    }
}
