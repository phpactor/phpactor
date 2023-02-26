<?php

namespace Phpactor\Extension\LanguageServerConfiguration\Listener;

use Generator;
use Phpactor\Configurator\Configurator;
use Phpactor\LanguageServerProtocol\MessageActionItem;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Event\Initialized;
use Psr\EventDispatcher\ListenerProviderInterface;
use function Amp\asyncCall;

class AutoConfigListener implements ListenerProviderInterface
{
    const YES = 'yes';
    const NO = 'no';


    public function __construct(private Configurator $configurator, private ClientApi $clientApi)
    {
    }


    /**
     * @return Generator<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        if ($event instanceof Initialized) {
            yield function (): void {
                $this->autoConfigure();
            };
        }
    }

    private function autoConfigure(): void
    {
        asyncCall(function () {
            $changes = 0;
            foreach ($this->configurator->suggestChanges() as $change) {
                $res = yield $this->clientApi->window()->showMessageRequest()->info(
                    $change->prompt(),
                    new MessageActionItem(self::YES),
                    new MessageActionItem(self::NO)
                );
                $this->configurator->apply($change, $res->title === self::YES);
            }
        });
    }
}
