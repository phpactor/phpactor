<?php

namespace Phpactor\Extension\Logger\Formatter;

use Monolog\Formatter\FormatterInterface;
use Psr\Log\LogLevel;

class PrettyFormatter implements FormatterInterface
{
    /**
     * {@inheritDoc}
     */
    public function format(array $record)
    {
        $message = sprintf(
            '[%s][%s] %s',
            $this->color($record['level_name']) . substr($record['level_name'], 0, 4)."\e[0;0m",
            "\e[1;37m".substr($record['datetime']->format('U.u'), 4)."\e[0;0m",
            $record['message']
        );

        $json = json_encode($record['context'], JSON_PRETTY_PRINT);
        return $message.$json."\n";
    }

    /**
     * {@inheritDoc}
     */
    public function formatBatch(array $records): void
    {
    }

    private function color(string $level): string
    {
        switch (strtolower($level)) {
            case LogLevel::EMERGENCY:
            case LogLevel::CRITICAL:
            case LogLevel::ERROR:
                return "\e[0;31m";
            case LogLevel::WARNING:
                return "\e[0;33m";
            case LogLevel::ALERT:
            case LogLevel::NOTICE:
                return "\e[0;36m";
            case LogLevel::INFO:
                return "\e[0;32m";
            case LogLevel::DEBUG:
                return "\e[0;37m";
        }

        return "\e[0;0m";
    }
}
