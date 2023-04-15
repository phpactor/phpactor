<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UndefinedVariableProvider;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;

class UndefinedVariableProviderTest extends DiagnosticsTestCase
{
    /**
     * @param Diagnostics<Diagnostic> $diagnostics
     */
    public function checkUndefinedVariable(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Undefined variable "$foo", did you mean one of "$zebra", "$foa", "$foo"', $diagnostics->at(0)->message());
    }

    /**
     * @param Diagnostics<Diagnostic> $diagnostics
     */
    public function checkVariableHasBeenAssigned(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    /**
     * @param Diagnostics<Diagnostic> $diagnostics
     */
    public function checkVariableIsThis(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    /**
     * @param Diagnostics<Diagnostic> $diagnostics
     */
    public function checkVariableIsParameter(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    protected function provider(): DiagnosticProvider
    {
        return new UndefinedVariableProvider();
    }
}
