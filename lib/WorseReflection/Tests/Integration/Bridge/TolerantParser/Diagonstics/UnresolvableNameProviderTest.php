<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnresolvableNameProvider;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;

class UnresolvableNameProviderTest extends DiagnosticsTestCase
{
    protected function provider(): DiagnosticProvider
    {
        return new UnresolvableNameProvider();
    }

    public function checkUnresolvableName(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Class "Foobar" not found', $diagnostics->at(0)->message());
    }

    public function checkUnresolvableFunction(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Function "foobar" not found', $diagnostics->at(0)->message());
    }

    public function checkUnresolvableNamespacedFunction(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Function "foobar" not found', $diagnostics->at(0)->message());
    }

    public function checkReservedNames(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }
}
