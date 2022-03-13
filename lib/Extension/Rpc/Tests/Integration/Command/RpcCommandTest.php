<?php

namespace Phpactor\Extension\Rpc\Tests\Integration\Command;

use PHPUnit\Framework\TestCase;
use Phpactor\Container\PhpactorContainer;
use Phpactor\Extension\Logger\LoggingExtension;
use Phpactor\Extension\Rpc\Command\RpcCommand;
use Phpactor\Extension\Rpc\RpcExtension;
use Phpactor\Extension\Rpc\RpcVersion;
use Phpactor\TestUtils\Workspace;
use Symfony\Component\Console\Tester\CommandTester;

class RpcCommandTest extends TestCase
{
    private Workspace $workspace;

    public function setUp(): void
    {
        $this->workspace = Workspace::create(__DIR__ . '/../../Workspace');
        $this->workspace->reset();
    }

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

        $tester = $this->execute($stdin);
        $this->assertEquals(0, $tester->getStatusCode());
        $response = json_decode($tester->getDisplay(), true);

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

        $tester = $this->execute($stdin, [ '--pretty' => true ]);
        $this->assertEquals(0, $tester->getStatusCode());
    }

    public function testReplaysLastRequest(): void
    {
        $randomString = md5(rand(0, 100000));
        $stdin = json_encode([
            'action' => 'echo',
            'parameters' => [
                'message' => $randomString,
            ],
        ]);

        $tester = $this->execute($stdin);
        $this->assertEquals(0, $tester->getStatusCode());

        $tester = $this->execute('', [ '--replay' => true ]);
        $this->assertEquals(0, $tester->getStatusCode());
        $response = json_decode($tester->getDisplay(), true);

        $this->assertEquals([
            'action' => 'echo',
            'parameters' => [
                'message' => $randomString,
            ],
            'version' => RpcVersion::asString(),
        ], $response);
    }

    private function execute(string $stdin, array $input = []): CommandTester
    {
        $container = PhpactorContainer::fromExtensions([
            LoggingExtension::class,
            RpcExtension::class
        ], []);

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $stdin);
        rewind($stream);
        $tester = new CommandTester(
            new RpcCommand(
                $container->get('rpc.request_handler'),
                $this->workspace()->path('/replay.json'),
                true,
                $stream
            )
        );
        $tester->execute($input);
        fclose($stream);

        return $tester;
    }

    private function workspace(): Workspace
    {
        return $this->workspace;
    }
}
