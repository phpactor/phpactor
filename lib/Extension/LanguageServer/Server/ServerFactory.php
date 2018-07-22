<?php

namespace Phpactor\Extension\LanguageServer\Server;

use Closure;
use InvalidArgumentException;
use Phpactor\Extension\LanguageServer\Server\Dispatcher\WriteRequestsToFileDispatcher;
use RuntimeException;

class ServerFactory
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $defaultAddress;

    /**
     * @var string
     */
    private $defaultPort;

    public function __construct(
        Dispatcher $dispatcher,
        string $defaultAddress,
        string $defaultPort
    )
    {
        $this->dispatcher = $dispatcher;
        $this->defaultAddress = $defaultAddress;
        $this->defaultPort = $defaultPort;
    }

    public function create(array $options): Server
    {
        $defaults = array_merge([
            'address' => $this->defaultAddress,
            'port' => $this->defaultPort,
            'info-message-callback' => function (string $message) {},
            'save-requests-to-file' => null,
        ]);

        if ($diff = array_diff(array_keys($options), array_keys($defaults))) {
            throw new InvalidArgumentException(sprintf(
                'Invalid options given to LSP server "%s", allowed options: "%s"',
                implode('", "', $diff), implode('", "', array_keys($defaults))
            ));
        }

        $options = array_merge($defaults, $options);

        $dispatcher = $this->dispatcher;

        if ($options['save-requests-to-file']) {
            $dispatcher = new WriteRequestsToFileDispatcher($dispatcher, $options['save-requests-to-file']);
        }

        return new Server(
            $dispatcher,
            $options['address'],
            $options['port'],
            $options['info-message-callback'],
            $options['save-requests-to-file']
        );
    }
}
