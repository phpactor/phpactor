<?php

namespace Phpactor\Tests\System\Console\Command;

use Phpactor\Tests\System\SystemTestCase;

class RpcCommandTest extends SystemTestCase
{
    /**
     * It should execute a command from stdin
     */
    public function testReadsFromStdin()
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
            'action' => 'echo',
            'parameters' => [
                'message' => 'Hello World',
            ],
        ], $response);
    }

    public function testReplaysLastRequest()
    {
        $randomString = md5(rand(0, 100000));
        $stdin = json_encode([
            'action' => 'echo',
            'parameters' => [
                'message' => $randomString,
            ],
        ]);

        $process = $this->phpactor('rpc', $stdin);
        $this->assertSuccess($process);

        $process = $this->phpactor('rpc --replay');
        $this->assertSuccess($process);
        $response = json_decode($process->getOutput(), true);

        $this->assertEquals([
            'action' => 'echo',
            'parameters' => [
                'message' => $randomString,
            ],
        ], $response);
    }
}
