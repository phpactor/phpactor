<?php

namespace Phpactor\Container;

use Closure;
use Phpactor\Container\Container;

interface ContainerBuilder
{
    public function register(string $serviceId, Closure $service, array $tags = []);

    public function build(array $parameters): Container;
}
