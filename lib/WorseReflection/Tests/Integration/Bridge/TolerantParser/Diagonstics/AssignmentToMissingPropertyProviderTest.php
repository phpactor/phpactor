<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\AssignmentToMissingPropertyProvider;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;

class AssignmentToMissingPropertyProviderTest extends DiagnosticsTestCase
{
    public function checkMissingProperty(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals(
            'Property "bar" has not been defined',
            $diagnostics->at(0)->message()
        );
    }

    public function checkNotMissingProperty(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    protected function provider(): DiagnosticProvider
    {
        return new AssignmentToMissingPropertyProvider();
    }
}
