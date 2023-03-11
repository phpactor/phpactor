<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\DeprecatedMemberAccessDiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;

class DeprecatedMemberAccessDiagnosticProviderTest extends DiagnosticsTestCase
{
    /**
     * @param Diagnostics<Diagnostic> $diagnostics
     */
    public function checkDeprecatedMethod(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        dump($diagnostics);
    }
    protected function provider(): DiagnosticProvider
    {
        return new DeprecatedMemberAccessDiagnosticProvider();
    }
}
