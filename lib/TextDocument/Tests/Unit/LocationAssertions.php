<?php

declare(strict_types=1);

namespace Phpactor\TextDocument\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Phpactor\TextDocument\Location;

trait LocationAssertions
{
    public static function assertLocation(Location $location, string $path, int $start, int $end): void
    {
        TestCase::assertEquals($path, $location->uri()->path());
        TestCase::assertEquals($start, $location->range()->start()->toInt(), 'Start position does not match.');
        TestCase::assertEquals($end, $location->range()->end()->toInt(), 'End position does not match.');
    }
}
