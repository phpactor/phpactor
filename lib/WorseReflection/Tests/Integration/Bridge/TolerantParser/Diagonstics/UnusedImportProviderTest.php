<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnusedImportProvider;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;

class UnusedImportProviderTest extends DiagnosticsTestCase
{
    protected function provider(): DiagnosticProvider
    {
        return new UnusedImportProvider();
    }

    public function checkUnusedImport(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
    }
}
