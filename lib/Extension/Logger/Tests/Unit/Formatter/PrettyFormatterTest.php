<?php

namespace Phpactor\Extension\Logger\Tests\Unit\Formatter;

use PHPUnit\Framework\Attributes\DataProvider;
use DateTime;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\Logger\Formatter\PrettyFormatter;

class PrettyFormatterTest extends TestCase
{
    #[DataProvider('provideFormat')]
    public function testFormat(array $record): void
    {
        $record = array_merge([
            'level_name' => 'info',
            'context' => [],
            'message' => 'hello',
            'datetime' => new DateTime(),
        ]);
        $formatter = new PrettyFormatter();
        $string = $formatter->format($record);
        self::assertIsString($string);
    }

    public static function provideFormat()
    {
        yield [
            ['level_name' => 'critical'],
        ];
        yield [
            ['level_name' => 'unknown'],
        ];
    }
}
