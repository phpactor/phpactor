<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingMethodProvider;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;

class MissingMethodProviderTest extends DiagnosticsTestCase
{
    public function checkInstanceMethod(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Method "bar" does not exist on class "Foobar"', $diagnostics->at(0)->message());
    }

    public function checkStaticMethod(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Method "bar" does not exist on class "Foobar"', $diagnostics->at(0)->message());
    }

    public function checkInlinedType(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    protected function provider(): DiagnosticProvider
    {
        return new MissingMethodProvider();
    }
}
