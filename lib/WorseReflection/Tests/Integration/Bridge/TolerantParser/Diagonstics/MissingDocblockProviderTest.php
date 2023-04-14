<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingDocblockReturnTypeProvider;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\Core\Diagnostics;

class MissingDocblockProviderTest extends DiagnosticsTestCase
{
    public function checkMissingDocblock(Diagnostics $diagnostics): void
    {
        self::assertCount(1, $diagnostics);
    }
    protected function provider(): DiagnosticProvider
    {
        return new MissingDocblockReturnTypeProvider();
    }
}
