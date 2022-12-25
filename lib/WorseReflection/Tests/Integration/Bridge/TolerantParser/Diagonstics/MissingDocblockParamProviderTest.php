<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingDocblockParamDiagnostic;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingDocblockParamProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingDocblockReturnTypeProvider;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;

class MissingDocblockParamProviderTest extends DiagnosticsTestCase
{
    public function checkMissingDocblockParam(Diagnostics $diagnostics): void
    {
        $diagnostics = $diagnostics->byClass(MissingDocblockParamDiagnostic::class);
        self::assertCount(1, $diagnostics);
        self::assertEquals('Method "foo" is missing docblock param: $foobar of type array<int,mixed>', $diagnostics->at(0)->message());
    }

    public function checkGenerator(Diagnostics $diagnostics): void
    {
        $diagnostics = $diagnostics->byClass(MissingDocblockParamDiagnostic::class);
        self::assertCount(1, $diagnostics);
        self::assertEquals('Method "foo" is missing docblock param: $foobar of type Generator<<missing>,string>', $diagnostics->at(0)->message());
    }

    public function checkNoDiagnosticIfParamIsPresent(Diagnostics $diagnostics): void
    {
        $diagnostics = $diagnostics->byClass(MissingDocblockParamDiagnostic::class);
        self::assertCount(0, $diagnostics);
    }

    protected function provider(): DiagnosticProvider
    {
        return new MissingDocblockParamProvider();
    }
}
