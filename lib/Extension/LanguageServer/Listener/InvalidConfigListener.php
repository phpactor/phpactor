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
    /**
     * @var ClientApi
     */
    private $clientApi;

    /**
     * @var ResolverErrors
     */
    private $errors;

    public function __construct(ClientApi $clientApi, ResolverErrors $errors)
    {
        $this->clientApi = $clientApi;
        $this->errors = $errors;
    }

    /**
     * {@inheritDoc}
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
