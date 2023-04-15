<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UndefinedVariableProvider;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;

class UndefinedVariableProviderTest extends DiagnosticsTestCase
{
    public function checkUndefinedVariable(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
    }
    protected function provider(): DiagnosticProvider
    {
        return new UndefinedVariableProvider();
    }
}
