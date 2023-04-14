<?php

namespace Phpactor\Extension\Laravel;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\OptionalExtension;
use Phpactor\Extension\CompletionWorse\CompletionWorseExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\Extension\Laravel\Completor\LaravelContainerCompletor;
use Phpactor\Extension\Laravel\Completor\LaravelViewCompletor;
use Phpactor\Extension\Laravel\WorseReflection\LaravelContainerContextResolver;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;

class LaravelExtension implements OptionalExtension
{
    public const DEV_TOOLS_EXECUTABLE = 'laravel.devtools.path';
    public const PARAM_CONTAINER_COMPLETOR_ENABLED = 'completion_worse.completor.laravel.container.enabled';
    public const PARAM_VIEW_COMPLETOR_ENABLED = 'completion_worse.completor.laravel.view.enabled';

    public function load(ContainerBuilder $container): void
    {
        $container->register(LaravelContainerInspector::class, function (Container $container) {
            $executablePath = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)
                                        ->resolve($container->getParameter(self::DEV_TOOLS_EXECUTABLE));
            $projectRoot = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)
                                     ->resolve('%project_root%');

            return new LaravelContainerInspector($executablePath, $projectRoot);
        });

        $container->register(LaravelContainerCompletor::class, function (Container $container) {
            return new LaravelContainerCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(LaravelContainerInspector::class)
            );
        }, [
            CompletionWorseExtension::TAG_TOLERANT_COMPLETOR => [
                'name' => 'laravel.container',
            ],
        ]);

        $container->register(LaravelViewCompletor::class, function (Container $container) {
            return new LaravelViewCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(LaravelContainerInspector::class)
            );
        }, [
            CompletionWorseExtension::TAG_TOLERANT_COMPLETOR => [
                'name' => 'laravel.view',
            ],
        ]);

        $container->register(LaravelContainerContextResolver::class, function (Container $container) {
            return new LaravelContainerContextResolver(
                $container->get(LaravelContainerInspector::class)
            );
        }, [
            WorseReflectionExtension::TAG_MEMBER_TYPE_RESOLVER => [
            ],
        ]);
    }


    public function configure(Resolver $schema): void
    {
        $schema->setDefaults([
            self::DEV_TOOLS_EXECUTABLE => '/Users/rob/.config/lsps/laravel-dev-generators/laravel-dev-tools',
            self::PARAM_CONTAINER_COMPLETOR_ENABLED => true,
            self::PARAM_VIEW_COMPLETOR_ENABLED => true,
        ]);
        $schema->setDescriptions([
            self::DEV_TOOLS_EXECUTABLE => 'Path to the Laravel dev tools executable.',
            self::PARAM_CONTAINER_COMPLETOR_ENABLED => 'Enable/disable the Laravel container completor - depends on Laravel extension being enabled',
            self::PARAM_VIEW_COMPLETOR_ENABLED => 'Enable/disable the Laravel view completor - depends on Laravel extension being enabled',
        ]);
    }

    public function name(): string
    {
        return 'laravel';
    }
}
