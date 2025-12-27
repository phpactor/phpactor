<?php

namespace Phpactor\Extension\LanguageServer\Logger;

use Phpactor\LanguageServer\Core\Server\ClientApi;
use Psr\Log\LoggerInterface;

class ClientLogger implements LoggerInterface
{
    public function __construct(
        private readonly ClientApi $client,
        private readonly LoggerInterface $innerLogger
    ) {
    }


    public function emergency($message, array $context = []): void
    {
        $this->client->window()->logMessage()->error($message);
        $this->innerLogger->emergency($message, $context);
    }


    public function alert($message, array $context = []): void
    {
        $this->client->window()->logMessage()->error($message);
        $this->innerLogger->alert($message, $context);
    }


    public function critical($message, array $context = []): void
    {
        $this->client->window()->logMessage()->error($message);
        $this->innerLogger->critical($message, $context);
    }


    public function error($message, array $context = []): void
    {
        $this->client->window()->showMessage()->error($message);
        $this->innerLogger->error($message, $context);
    }


    public function warning($message, array $context = []): void
    {
        $this->innerLogger->warning($message, $context);
    }


    public function notice($message, array $context = []): void
    {
        $this->innerLogger->notice($message, $context);
    }


    public function info($message, array $context = []): void
    {
        $this->innerLogger->info($message, $context);
    }


    public function debug($message, array $context = []): void
    {
        $this->innerLogger->debug($message, $context);
    }


    public function log($level, $message, array $context = []): void
    {
        $this->innerLogger->log($level, $message, $context);
    }
}
