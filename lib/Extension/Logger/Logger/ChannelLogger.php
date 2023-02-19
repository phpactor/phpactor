<?php

namespace Phpactor\Extension\Logger\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class ChannelLogger extends AbstractLogger
{
    private int $pid;

    public function __construct(private string $name, private LoggerInterface $innerLogger)
    {
        $this->pid = getmypid() ?: 0;
    }

    public function log($level, $message, array $context = []): void
    {
        $this->innerLogger->log(
            $level,
            $message,
            array_merge([
                'pid' => $this->pid,
                'channel' => $this->name,
            ], $context),
        );

    }
}
