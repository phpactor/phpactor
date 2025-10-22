<?php

namespace Phpactor\Extension\OpenTelemetry;

use Phpactor\Container\BootableExtension;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Container\OptionalExtension;
use Phpactor\Extension\OpenTelemetry\Model\HookBootstrap;
use Phpactor\Extension\OpenTelemetry\Model\HookProvider;
use Phpactor\MapResolver\Resolver;

final class OpenTelemetryExtension implements Extension, BootableExtension, OptionalExtension
{
    const SERVICE_HOOK_PROVIDERS = 'open_telemetry.hook_providers';
    const TAG_HOOK_PROVIDER = 'open.telemetry.hook_provider';

    public function load(ContainerBuilder $container): void
    {
        $container->register(HookBootstrap::class, function (Container $container) {
            $providers = [];
            foreach ($container->getServiceIdsForTag(self::TAG_HOOK_PROVIDER) as $serviceId => $_) {
                $providers[] = $container->expect($serviceId, HookProvider::class);
            }
            return new HookBootstrap($providers);
        });

    }

    public function configure(Resolver $schema): void
    {
    }

    public function boot(Container $container): void
    {
        $container->get(HookBootstrap::class)->bootstrap();
    }

    public function name(): string
    {
        return 'open_telemetry';
    }
}
