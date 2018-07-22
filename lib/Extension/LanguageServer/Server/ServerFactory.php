<?php

namespace Phpactor\Extension\LanguageServer\Server;

use Closure;
use InvalidArgumentException;
use Phpactor\Extension\LanguageServer\Server\Dispatcher\WriteRequestsToFileDispatcher;
use Phpactor\Extension\LanguageServer\Server\ProtocolIO\StdIO;
use Psr\Log\LoggerInterface;
use RuntimeException;

class ServerFactory
{
    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Dispatcher $dispatcher,
        LoggerInterface $logger
    )
    {
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    public function create(array $options = []): Server
    {
        $defaults = array_merge([]);

        if ($diff = array_diff(array_keys($options), array_keys($defaults))) {
            throw new InvalidArgumentException(sprintf(
                'Invalid options given to LSP server "%s", allowed options: "%s"',
                implode('", "', $diff), implode('", "', array_keys($defaults))
            ));
        }

        $options = array_merge($defaults, $options);

        $dispatcher = $this->dispatcher;

        return new Server(
            $dispatcher,
            new StdIO($this->logger),
            $this->logger
        );
    }
}
