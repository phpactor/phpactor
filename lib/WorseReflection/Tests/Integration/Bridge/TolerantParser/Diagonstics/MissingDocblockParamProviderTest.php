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
    }
    protected function provider(): DiagnosticProvider
    {
        return new MissingDocblockParamProvider();
    }
}
