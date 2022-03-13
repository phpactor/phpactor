<?php

namespace Phpactor\Extension\LanguageServerIndexer\Tests\Extension;

use Phpactor\AmpFsWatch\Exception\WatcherDied;
use Phpactor\AmpFsWatch\ModifiedFileQueue;
use Phpactor\AmpFsWatch\Watcher\TestWatcher\TestWatcher;
use Phpactor\Container\Container;
use Phpactor\Container\ContainerBuilder;
use Phpactor\Container\Extension;
use Phpactor\Indexer\Extension\IndexerExtension;
use Phpactor\MapResolver\Resolver;

class TestExtension implements Extension
{
    public function load(ContainerBuilder $container): void
    {
        $container->register('test.watcher.will_die', function (Container $container) {
            return new TestWatcher(new ModifiedFileQueue(), 0, new WatcherDied('No'));
        }, [
            IndexerExtension::TAG_WATCHER => [
                'name' => 'will_die',
            ],
        ]);
    }

    
    public function configure(Resolver $schema): void
    {
    }
}
