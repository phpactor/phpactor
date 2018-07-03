<?php

namespace Phpactor\Container;

use Phpactor\MapResolver\Resolver;

interface Extension
{
    /**
     * Register services with the container.
     *
     * @param Container $container
     */
    public function load(ContainerBuilder $container);

    /**
     * Return the default parameters for the container.
     *
     * @return array
     */
    public function configure(Resolver $schema);
}
