<?php

namespace Phpactor\Extension\Logger\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class ChannelLogger extends AbstractLogger
{
    public function __construct(
        private string $name,
        private LoggerInterface $innerLogger
    ) {
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
