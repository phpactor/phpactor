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
            'actions' => [
                [
                    'action' => 'echo',
                    'parameters' => [
                        'message' => 'Hello World',
                    ],
                ],
                [
                    'action' => 'echo',
                    'parameters' => [
                        'message' => 'Goodbye World!',
                    ],
                ],
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
            [
                'action' => 'echo',
                'parameters' => [
                    'message' => 'Goodbye World!',
                ],
            ]
        ], $response['actions']);
    }
}
