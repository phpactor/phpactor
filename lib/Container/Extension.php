<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\ExtensionInterface;
use Phpactor\Container\ContainerBuilder;

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
    public function configure(Schema $schema);
}
