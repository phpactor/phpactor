<?php

namespace Phpactor\Extension\Laravel;

use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\OptionalExtension;
use Phpactor\Extension\CompletionWorse\CompletionWorseExtension;
use Phpactor\Extension\FilePathResolver\FilePathResolverExtension;
use Phpactor\Extension\Laravel\Adapter\Laravel\LaravelContainerInspector;
use Phpactor\Extension\Laravel\Completor\LaravelContainerCompletor;
use Phpactor\Extension\Laravel\WorseReflection\LaravelContainerContextResolver;
use Phpactor\Extension\WorseReflection\WorseReflectionExtension;
use Phpactor\MapResolver\Resolver;

class LaravelExtension implements OptionalExtension
{
    const XML_PATH = 'laravel.services.path';
    const PARAM_COMPLETOR_ENABLED = 'completion_worse.completor.laravel.enabled';

    public function load(ContainerBuilder $container): void
    {
        $container->register(LaravelContainerInspector::class, function (Container $container) {
            $xmlPath = $container->get(FilePathResolverExtension::SERVICE_FILE_PATH_RESOLVER)->resolve($container->getParameter(self::XML_PATH));
            return new LaravelContainerInspector($xmlPath);
        });
        $container->register(LaravelContainerCompletor::class, function (Container $container) {
            return new LaravelContainerCompletor(
                $container->get(WorseReflectionExtension::SERVICE_REFLECTOR),
                $container->get(LaravelContainerInspector::class)
            );
        }, [
            CompletionWorseExtension::TAG_TOLERANT_COMPLETOR => [
                'name' => 'laravel',
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
            self::XML_PATH => '%project_root%/bootstrap/cache/services.php',
            self::PARAM_COMPLETOR_ENABLED => true,
        ]);
        $schema->setDescriptions([
            self::XML_PATH => 'Path to the Laravel services cache.',
            self::PARAM_COMPLETOR_ENABLED => 'Enable/disable the Laravel completor - depends on Laravel extension being enabled',
        ]);
    }

    public function name(): string
    {
        return 'laravel';
    }
}
