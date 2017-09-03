<?php

namespace Phpactor\Container;

use PhpBench\DependencyInjection\ExtensionInterface;
use PhpBench\DependencyInjection\Container;


class ContextActionExtension implements ExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(Container $container)
    {
        $this->registerActions($container);
        $this->registerCommands($container);
    }

    private function registerActions(Container $actions)
    {
        $container->register('context_action
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultConfig()
    {
    }
}

