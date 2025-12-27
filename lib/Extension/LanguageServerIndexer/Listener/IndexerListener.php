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
    public function __construct(private readonly ServiceManager $manager)
    {
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
            yield function (): void {
                if ($this->manager->isRunning(IndexerHandler::SERVICE_INDEXER)) {
                    $this->manager->stop(IndexerHandler::SERVICE_INDEXER);
                }
            };
        }
    }
}
