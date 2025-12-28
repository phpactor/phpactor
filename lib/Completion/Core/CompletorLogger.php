<?php

namespace Phpactor\Completion\Core;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class CompletorLogger
{
    public function __construct(private LoggerInterface $logger = new NullLogger())
    {
    }

    public function timeTaken(object $completor, float $time): void
    {
        $this->logger->info(sprintf(
            'COMP %s %s%s',
            number_format($time, 4),
            substr($completor::class, strrpos($completor::class, '\\') + 1),
            $completor instanceof CompletorDecorator ? '/' . $completor->decorates() : '',
        ));
    }
}
