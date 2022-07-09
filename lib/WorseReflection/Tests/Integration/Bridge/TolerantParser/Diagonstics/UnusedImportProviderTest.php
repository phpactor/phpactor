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
        self::assertEquals('Name "Foobar" is imported but not used', $diagnostics->at(0)->message());
    }

    public function checkUsedImport(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    public function checkNamespacedUnusedImports(Diagnostics $diagnostics): void
    {
        self::assertCount(2, $diagnostics);
    }

    public function checkNamespacedUsedImports(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }
}
