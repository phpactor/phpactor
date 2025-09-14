<?php

namespace Phpactor\OpenTelemetry;

use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\MapResolver\Resolver;

final class OpenTelemetryExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
    }

    public function configure(Resolver $schema): void
    {
    }
}
