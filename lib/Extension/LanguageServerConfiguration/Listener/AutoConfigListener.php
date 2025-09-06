<?php

namespace Phpactor\Extension\LanguageServerConfiguration\Listener;

use Generator;
use Phpactor\Configurator\Configurator;
use Phpactor\Extension\Core\Trust\Trust;
use Phpactor\LanguageServerProtocol\MessageActionItem;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Event\Initialized;
use Psr\EventDispatcher\ListenerProviderInterface;
use function Amp\asyncCall;
use function Amp\delay;

class AutoConfigListener implements ListenerProviderInterface
{
    const YES = 'yes';
    const NO = 'no';

    public function __construct(private Configurator $configurator, private ClientApi $clientApi, Trust $trust)
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
                $changes++;

                // artificial delay to prevent neovim from producing concatenated prompts
                yield delay(100);
            }

            if ($changes) {
                $this->clientApi->window()->showMessage()->info(sprintf('%d changes applied to .phpactor.json, restart the language server for them to take effect', $changes));
            }
        });
    }
}
