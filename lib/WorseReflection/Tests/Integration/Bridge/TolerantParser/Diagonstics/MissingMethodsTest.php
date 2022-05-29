<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingMethods;
use Phpactor\WorseReflection\Core\DiagnosticProvider;

class MissingMethodsTest extends DiagnosticsTestCase
{
    protected function provider(): DiagnosticProvider
    {
        return new MissingMethods();
    }
}
