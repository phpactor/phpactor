<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\UnusedImportProvider;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;

class UnusedImportProviderTest extends DiagnosticsTestCase
{
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
        self::assertCount(1, $diagnostics);
    }

    public function checkNamespacedUsedImports(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    public function checkClassImportedInOneNamespaceButUsedInAnother(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Name "Foo" is imported but not used', $diagnostics->at(0)->message());
    }

    public function checkCompactUseUnused(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Name "Barfoo" is imported but not used', $diagnostics->at(0)->message());
    }

    public function checkAliasedImportForUsedClass(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    public function checkAliasedImportForUnusedClass(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
        self::assertEquals('Name "Bazgar" is imported but not used', $diagnostics->at(0)->message());
    }

    public function checkGh1866(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    public function checkUsedByAnnotation(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    public function checkUsedByNamespacedAnnotation(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    public function checkUsedByDoctrineAnnotation(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    public function checkUsedByThrowsAnnotation(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    public function checkUsedByDoctrineAnnotationAliased(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    public function checkUsedByDoctrineAttributeAliased(Diagnostics $diagnostics): void
    {
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            $this->markTestSkipped('Requires PHP >= 8.0');
        }

        self::assertCount(0, $diagnostics);
    }

    public function checkUsedByDocblockComplex(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    public function checkTrait(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    public function checkCompactNamespacedUse(Diagnostics $diagnostics): void
    {
        self::assertCount(0, $diagnostics);
    }

    protected function provider(): DiagnosticProvider
    {
        return new UnusedImportProvider();
    }
}
