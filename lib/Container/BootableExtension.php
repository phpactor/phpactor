<?php

namespace Phpactor\Container;

interface BootableExtension
{
    public function boot(Container $container): void;
}
