<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingMethodProvider;
use Phpactor\WorseReflection\Core\DiagnosticProvider;

class MissingMethodProviderTest extends DiagnosticsTestCase
{
    protected function provider(): DiagnosticProvider
    {
        return new MissingMethodProvider();
    }
}
