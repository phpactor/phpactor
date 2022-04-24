<?php

namespace Phpactor\Extension\Logger;

use Phpactor\Extension\Logger\Logger\ChannelLogger;
use Psr\Log\LoggerInterface;

class LoggerFactory
{
    private LoggerInterface $mainLogger;

    public function __construct(LoggerInterface $mainLogger)
    {
        $this->mainLogger = $mainLogger;
    }
         
    public function get(string $name): LoggerInterface
    {
        return new ChannelLogger($name, $this->mainLogger);
    }
}
