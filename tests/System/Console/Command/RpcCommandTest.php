<?php

namespace Phpactor\Tests\System\Console\Command;

use Phpactor\Tests\System\SystemTestCase;

class RpcCommandTest extends SystemTestCase
{
    /**
     * It should execute a command from stdin
     */
    public function testFromStdIn()
    {
        $stdin = json_encode([
            'action' => 'echo',
            'parameters' => [
                'message' => 'Hello World',
            ],
        ]);

        $process = $this->phpactor('rpc', $stdin);
        $this->assertSuccess($process);
        $response = json_decode($process->getOutput(), true);

        $this->assertEquals([
            [
                'action' => 'echo',
                'parameters' => [
                    'message' => 'Hello World',
                ],
            ],
        ], $response['actions']);
    }
}
