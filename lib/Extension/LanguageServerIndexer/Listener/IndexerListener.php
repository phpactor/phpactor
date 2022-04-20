<?php

namespace Phpactor\Extension\LanguageServerIndexer\Listener;

use Generator;
use Phpactor\Extension\LanguageServerIndexer\Event\IndexReset;
use Phpactor\Extension\LanguageServerIndexer\Handler\IndexerHandler;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Phpactor\LanguageServer\Event\WillShutdown;
use Psr\EventDispatcher\ListenerProviderInterface;

class IndexerListener implements ListenerProviderInterface
{
    private ServiceManager $manager;

    public function __construct(ServiceManager $manager)
    {
        $this->manager = $manager;
    }

    
    /**
     * @return Generator<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        if ($event instanceof IndexReset) {
            yield function (): void {
                if ($this->manager->isRunning(IndexerHandler::SERVICE_INDEXER)) {
                    $this->manager->stop(IndexerHandler::SERVICE_INDEXER);
                }
                $this->manager->start(IndexerHandler::SERVICE_INDEXER);
            };
        }

        if ($event instanceof WillShutdown) {
            if ($this->manager->isRunning(IndexerHandler::SERVICE_INDEXER)) {
                $this->manager->stop(IndexerHandler::SERVICE_INDEXER);
            }
        }
    }
}
