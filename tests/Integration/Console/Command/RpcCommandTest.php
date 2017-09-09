<?php

namespace Phpactor\Tests\Integration\Console\Command;

use Phpactor\Tests\Integration\SystemTestCase;

class RpcCommandTest extends SystemTestCase
{
    /**
     * It should execute a command from stdin
     */
    public function testFromStdIn()
    {
        $stdin = json_encode([
            [
                'command' => 'greet',
                'arguments' => [
                    'hello' => 'smith',
                ],
                'options' => [
                    'title' => 'mr',
                ],
            ],
            [
                'command' => 'greet',
                'arguments' => [
                    'hello' => 'smith',
                ],
                'options' => [
                    'title' => 'mrs',
                ],
            ],
        ]);

        $process = $this->phpactor('rpc', $stdin);
        $this->assertSuccess($process);
        $response = json_decode($process->getOutput());

        $this->assertEquals([
            [
                'action' => 'greet',
                'arguments' => [
                    'greeting' => 'hello mr smith',
                ],
            ],
            [
                'action' => 'greet',
                'arguments' => [
                    'greeting' => 'hello mrs smith',
                ],
            ],
        ], $response['actions']);
    }
}
