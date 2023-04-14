<?php

namespace Phpactor\Extension\LanguageServer\Listener;

use Amp\Promise;
use Amp\Success;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Event\Initialized;
use Phpactor\MapResolver\InvalidMap;
use Phpactor\MapResolver\ResolverErrors;
use Psr\EventDispatcher\ListenerProviderInterface;

class InvalidConfigListener implements ListenerProviderInterface
{
    public function __construct(private ClientApi $clientApi, private ResolverErrors $errors)
    {
    }

    /**
     * @return iterable<callable>
     */
    public function getListenersForEvent(object $event): iterable
    {
        if ($event instanceof Initialized) {
            return [[$this, 'handleInvalidConfig']];
        }

        return [];
    }

    /**
     * @return Success<null>
     */
    public function handleInvalidConfig(): Promise
    {
        if ($this->errors->errors()) {
            $this->clientApi->window()->showMessage()->warning(sprintf(
                'Phpactor configuration error: %s',
                implode(', ', array_map(function (InvalidMap $error) {
                    return $error->getMessage();
                }, $this->errors->errors()))
            ));
        }
        return new Success();
    }
}
