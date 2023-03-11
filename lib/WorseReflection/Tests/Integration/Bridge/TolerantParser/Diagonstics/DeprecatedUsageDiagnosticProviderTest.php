<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\DeprecatedUsageDiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;

class DeprecatedUsageDiagnosticProviderTest extends DiagnosticsTestCase
{
    /**
     * @param Diagnostics<Diagnostic> $diagnostics
     */
    public function checkDeprecatedMethod(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Call to deprecated method "deprecated": This is deprecated', $diagnostics->at(0)->message());
    }

    /**
     * @param Diagnostics<Diagnostic> $diagnostics
     */
    public function checkDeprecatedProperty(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Call to deprecated property "deprecated": This is deprecated', $diagnostics->at(0)->message());
    }

    /**
     * @param Diagnostics<Diagnostic> $diagnostics
     */
    public function checkDeprecatedConstant(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Call to deprecated constant "FOO": This is deprecated', $diagnostics->at(0)->message());
    }

    /**
     * @param Diagnostics<Diagnostic> $diagnostics
     */
    public function checkDeprecatedMethodOnTrait(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Call to deprecated method "deprecated": This is deprecated', $diagnostics->at(0)->message());
    }

    /**
     * @param Diagnostics<Diagnostic> $diagnostics
     */
    public function checkDeprecatedClass(Diagnostics $diagnostics): void
    {
        self::assertCount(2, $diagnostics);
        self::assertEquals('Call to deprecated class "Deprecated"', $diagnostics->at(0)->message());
    }

    /**
     * @param Diagnostics<Diagnostic> $diagnostics
     */
    public function checkDeprecatedEnum(Diagnostics $diagnostics): void
    {
        if (version_compare(PHP_VERSION, '8.1', '<')) {
            return;
        }

        self::assertCount(2, $diagnostics);
        self::assertEquals('Call to deprecated enum "Deprecated"', $diagnostics->at(0)->message());
    }

    /**
     * @param Diagnostics<Diagnostic> $diagnostics
     */
    public function checkDeprecatedFunction(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Call to deprecated function "bar"', $diagnostics->at(0)->message());
    }

    protected function provider(): DiagnosticProvider
    {
        return new DeprecatedUsageDiagnosticProvider();
    }
}
