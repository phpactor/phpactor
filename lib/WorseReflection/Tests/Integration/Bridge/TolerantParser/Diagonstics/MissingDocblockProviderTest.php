<?php

namespace Phpactor\WorseReflection\Tests\Integration\Bridge\TolerantParser\Diagonstics;

use Phpactor\WorseReflection\Bridge\Phpactor\DocblockParser\DocblockParserFactory;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingDocblockProvider;
use Phpactor\WorseReflection\Bridge\TolerantParser\Diagnostics\MissingMethodProvider;
use Phpactor\WorseReflection\Core\DiagnosticProvider;
use Phpactor\WorseReflection\ReflectorBuilder;

class MissingDocblockProviderTest extends DiagnosticsTestCase
{
    protected function provider(): DiagnosticProvider
    {
        return new MissingDocblockProvider(
            new DocblockParserFactory(ReflectorBuilder::create()->build())
        );
    }
}
