<?php

namespace Phpactor\Extension\LanguageServer\Listener;

use Phpactor\LanguageServer\Core\Server\Exception\ExitSession;
use Phpactor\LanguageServer\Event\WillShutdown;
use Psr\EventDispatcher\ListenerProviderInterface;
use function Amp\asyncCall;
use function Amp\delay;

class SelfDestructListener implements ListenerProviderInterface
{
    public function __construct(private int $selfDestructTimeout)
    {
    }

    /**
     * @return array<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        if ($event instanceof WillShutdown) {
            return [
                [$this, 'handleShutdown']
            ];
        }

        return [];
    }

    public function handleShutdown(WillShutdown $willShutdown): void
    {
        asyncCall(function () {
            yield delay($this->selfDestructTimeout);
            throw new ExitSession(sprintf(
                'Waited "%s" milliseconds after shutdown request for exit notification but did not get one so I\'m self destructing.',
                $this->selfDestructTimeout
            ));
        });
    }
}
