<?php

namespace Phpactor\Container;

use Closure;

interface ContainerBuilder
{
    public function register(string $serviceId, Closure $service, array $tags = []);

    public function build(array $parameters): Container;
}
