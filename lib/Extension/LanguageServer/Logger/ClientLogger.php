<?php

namespace Phpactor\Extension\LanguageServer\Logger;

use Phpactor\LanguageServer\Core\Server\ClientApi;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class ClientLogger implements LoggerInterface
{
    private LoggerInterface $innerLogger;

    private ClientApi $client;

    public function __construct(ClientApi $client, LoggerInterface $innerLogger)
    {
        $this->innerLogger = $innerLogger;
        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function emergency($message, array $context = [])
    {
        $this->client->window()->logMessage()->error($message);
        $this->innerLogger->emergency($message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function alert($message, array $context = [])
    {
        $this->client->window()->logMessage()->error($message);
        $this->innerLogger->alert($message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function critical($message, array $context = [])
    {
        $this->client->window()->logMessage()->error($message);
        $this->innerLogger->critical($message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function error($message, array $context = [])
    {
        $this->client->window()->showMessage()->error($message);
        $this->innerLogger->error($message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function warning($message, array $context = [])
    {
        $this->innerLogger->warning($message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function notice($message, array $context = [])
    {
        $this->innerLogger->notice($message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function info($message, array $context = [])
    {
        $this->innerLogger->info($message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function debug($message, array $context = [])
    {
        $this->innerLogger->debug($message, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = [])
    {
        $this->innerLogger->log($message, $context);
    }
}
