<?php

namespace Phpactor\Extension\Logger\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class ChannelLogger extends AbstractLogger
{
    private LoggerInterface $innerLogger;

    private string $name;

    public function __construct(string $name, LoggerInterface $innerLogger)
    {
        $this->innerLogger = $innerLogger;
        $this->name = $name;
    }

    public function log($level, $message, array $context = []): void
    {
        $this->innerLogger->log(
            $level,
            $message,
            array_merge([
                'channel' => $this->name,
            ], $context),
        );
    }
}
