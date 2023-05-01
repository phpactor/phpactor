<?php

namespace Phpactor\Tests\Unit\Extension\Core\Rpc;

use Phpactor\ConfigLoader\Adapter\PathCandidate\AbsolutePathCandidate;
use Phpactor\ConfigLoader\Core\PathCandidates;
use Phpactor\Extension\Rpc\Response\ReturnResponse;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Core\Application\Status;
use Prophecy\Prophecy\ObjectProphecy;
use Phpactor\Extension\Rpc\Response\EchoResponse;
use Phpactor\Extension\Core\Rpc\StatusHandler;

class StatusHandlerTest extends HandlerTestCase
{
    /**
     * @var ObjectProphecy<Status>
     */
    private ObjectProphecy $status;

    private ObjectProphecy $paths;

    public function setUp(): void
    {
        $this->status = $this->prophesize(Status::class);
        $this->paths = $this->prophesize(PathCandidates::class);
    }

    public function createHandler(): Handler
    {
        return new StatusHandler(
            $this->status->reveal(),
            $this->paths->reveal()
        );
    }

    public function testMessageStatus(): void
    {
        $this->status->check()->willReturn([
            'php_version' => '7.1',
            'phpactor_version' => 'version one',
            'phpactor_is_develop' => true,
            'cwd' => '/path/to/here',
            'good' => [ 'i am good' ],
            'bad' => [ 'i am bad' ],
        ]);
        $this->paths->getIterator()->will(function () {
            yield new AbsolutePathCandidate('/config/file1.yml', 'yml');
            yield new AbsolutePathCandidate('/config/file2.yml', 'yml');
        });

        $response = $this->handle('status', []);
        $this->assertInstanceOf(EchoResponse::class, $response);
    }

    public function testDetailStatus(): void
    {
        $this->status->check()->willReturn([
            'php_version' => '7.1',
            'phpactor_version' => 'version one',
            'cwd' => '/path/to/here',
            'good' => ['i am good'],
            'bad' => ['i am bad'],
            'config_files' => [
                '/config/file1.yml' => true,
                '/config/file2.yml' => false,
            ],
            'filesystems' => ['git', 'simple'],
        ]);

        $expected = [
            'php_version' => '7.1',
            'phpactor_version' => 'version one',
            'cwd' => '/path/to/here',
            'config_files' => [
                '/config/file1.yml' => true,
                '/config/file2.yml' => false,
            ],
            'filesystems' => ['git', 'simple'],
            'diagnostics' => [
                'i am good' => true,
                'i am bad' => false,
            ],
        ];

        $response = $this->handle('status', ['type' => 'detailed']);
        $this->assertInstanceOf(ReturnResponse::class, $response);
        $this->assertEquals($expected, $response->value());
    }
}
