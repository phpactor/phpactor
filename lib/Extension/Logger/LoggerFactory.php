<?php

namespace Phpactor\Extension\Logger;

use Phpactor\Extension\Logger\Logger\ChannelLogger;
use Psr\Log\LoggerInterface;

class LoggerFactory
{
    public function __construct(private readonly LoggerInterface $mainLogger)
    {
    }

    public function get(string $name): LoggerInterface
    {
        return new ChannelLogger($name, $this->mainLogger);
    }
}
