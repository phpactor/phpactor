<?php

namespace Phpactor\Tests\System\Extension\Rpc\Command;

use Phpactor\Extension\Rpc\RpcVersion;
use Phpactor\Tests\System\SystemTestCase;

class RpcCommandTest extends SystemTestCase
{
    /**
     * It should execute a command from stdin
     */
    public function testReadsFromStdin(): void
    {
        $stdin = json_encode([
            'action' => 'echo',
            'parameters' => [
                'message' => 'Hello World',
            ],
        ]);

        $process = $this->phpactorFromStringArgs('rpc', $stdin);
        $this->assertSuccess($process);

        $response = json_decode($process->getOutput(), true);

        $this->assertEquals([
            'action' => 'echo',
            'parameters' => [
                'message' => 'Hello World',
            ],
            'version' => RpcVersion::asString(),
        ], $response);
    }

    public function testPrettyPrintsOutput(): void
    {
        $stdin = json_encode([
            'action' => 'echo',
            'parameters' => [
                'message' => 'Hello World',
            ],
        ]);

        $process = $this->phpactorFromStringArgs('rpc --pretty', $stdin);
        $this->assertSuccess($process);
    }

    public function testReplaysLastRequest(): void
    {
        // enable the feature
        file_put_contents($this->workspace()->path('.phpactor.yml'), 'rpc.store_replay: true');

        $randomString = md5(rand(0, 100000));
        $stdin = json_encode([
            'action' => 'echo',
            'parameters' => [
                'message' => $randomString,
            ],
        ]);

        $process = $this->phpactorFromStringArgs('rpc', $stdin);
        $this->assertSuccess($process);

        $process = $this->phpactorFromStringArgs('rpc --replay');
        $this->assertSuccess($process);
        $response = json_decode($process->getOutput(), true);

        $this->assertEquals([
            'action' => 'echo',
            'parameters' => [
                'message' => $randomString,
            ],
            'version' => RpcVersion::asString(),
        ], $response);
    }
}
