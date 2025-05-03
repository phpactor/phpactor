<?php

namespace Phpactor;

final class Stats
{
    /**
     * @var array<string,int>
     */
    private static array $counters = [];

    public static function inc(string $counter): void
    {
        if (!isset(self::$counters[$counter])) {
            self::$counters[$counter] = 0;
        }
        self::$counters[$counter]++;
    }

    /**
     * @return array<string,int>
     */
    public static function toArray(): array
    {
        return self::$counters;
    }
}
