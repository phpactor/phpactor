<?php

namespace Phpactor\Extension\LanguageServer\Tests\Unit\Handler;

use Phpactor\Extension\LanguageServer\Handler\DebugHandler;
use Phpactor\Extension\LanguageServer\Tests\Unit\LanguageServerTestCase;

class DebugHandlerTest extends LanguageServerTestCase
{
    public function testDumpConfig(): Void
    {
        $tester = $this->createTester();
        $response = $tester->mustRequestAndWait(DebugHandler::METHOD_DEBUG_CONFIG, []);
        $this->assertSuccess($response);
    }

    public function testDumpConfigReturningAsJson(): Void
    {
        $tester = $this->createTester();
        $response = $tester->mustRequestAndWait(DebugHandler::METHOD_DEBUG_CONFIG, [
            'return' => true,
        ]);
        $this->assertSuccess($response);

        $result = $response->result;
        self::assertIsString($result);
        self::assertJson($result);
    }

    public function testDumpWorkspace(): void
    {
        $tester = $this->createTester();
        $response = $tester->mustRequestAndWait(DebugHandler::METHOD_DEBUG_WORKSPACE, []);
        $this->assertSuccess($response);
    }

    public function testStatus(): void
    {
        $tester = $this->createTester();
        $response = $tester->mustRequestAndWait(DebugHandler::METHOD_DEBUG_STATUS, []);
        $this->assertSuccess($response);
    }
}
