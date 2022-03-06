<?php

namespace Phpactor\Extension\LanguageServerReferenceFinder\Tests\Extension;

use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Indexer\IndexAgentBuilder;
use Phpactor\Indexer\Model\Indexer;
use Phpactor\MapResolver\Resolver;

class TestIndexerExtension implements Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(ContainerBuilder $container): void
    {
        $container->register(Indexer::class, function () {
            return IndexAgentBuilder::create(
                __DIR__ . '/../Workspace',
                __DIR__ . '/../Workspace',
            )->buildTestAgent()->indexer();
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configure(Resolver $schema): void
    {
    }
}
