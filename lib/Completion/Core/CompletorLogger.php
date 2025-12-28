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
            'COMP %s %s',
            number_format($time, 4),
            self::format($completor),
        ));
    }

    private static function format(object $completor): string
    {
        $shortName =  substr($completor::class, strrpos($completor::class, '\\') + 1);

        if (!$completor instanceof CompletorDecorator) {
            return $shortName;
        }

        return $shortName . '/' . self::format($completor->decorates());
    }
}
