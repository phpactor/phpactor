<?php

namespace Phpactor\Extension\LanguageServerIndexer\Listener;

use Phpactor\Extension\LanguageServerIndexer\Event\IndexReset;
use Phpactor\Extension\LanguageServerIndexer\Handler\IndexerHandler;
use Phpactor\LanguageServer\Core\Service\ServiceManager;
use Psr\EventDispatcher\ListenerProviderInterface;

class ReindexListener implements ListenerProviderInterface
{
    /**
     * @var ServiceManager
     */
    private $manager;

    public function __construct(ServiceManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritDoc}
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
    }
}
