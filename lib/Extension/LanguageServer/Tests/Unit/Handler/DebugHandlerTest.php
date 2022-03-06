<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit\Handler;

use Phpactor\Extension\LanguageServer\Handler\DebugHandler;
use Phpactor\Extension\LanguageServer\Tests\Unit\LanguageServerTestCase;

class DebugHandlerTest extends LanguageServerTestCase
{
    public function testDumpConfig(): Void
    {
        $tester = $this->createTester();
        $response = $tester->requestAndWait(DebugHandler::METHOD_DEBUG_CONFIG, []);
        $this->assertSuccess($response);
    }

    public function testDumpWorkspace(): void
    {
        $tester = $this->createTester();
        $response = $tester->requestAndWait(DebugHandler::METHOD_DEBUG_WORKSPACE, []);
        $this->assertSuccess($response);
    }
}
