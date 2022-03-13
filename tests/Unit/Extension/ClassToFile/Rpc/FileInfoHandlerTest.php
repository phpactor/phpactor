<?php

namespace Phpactor\Tests\Unit\Extension\ClassToFile\Rpc;

use Phpactor\Extension\ClassToFileExtra\Application\FileInfo;
use Phpactor\Extension\ClassToFileExtra\Rpc\FileInfoHandler;
use Phpactor\Extension\Rpc\Handler;
use Phpactor\Extension\Rpc\Response\ReturnResponse;
use Phpactor\Tests\Unit\Extension\Rpc\HandlerTestCase;
use Prophecy\Prophecy\ObjectProphecy;

class FileInfoHandlerTest extends HandlerTestCase
{
    private ObjectProphecy $fileInfo;

    public function setUp(): void
    {
        $this->fileInfo = $this->prophesize(FileInfo::class);
    }

    public function testReturnsAResponseWithAFileInfo(): void
    {
        $path =  'src/Controller/BlogController.php';
        $result = [
            'class' => 'App\Controller\BlogController',
            'class_name' => 'BlogController',
            'class_namespace' => 'App\Controller',
        ];

        $this->fileInfo->infoForFile($path)->willReturn($result);

        $response = $this->handle('file_info', ['path' => $path]);

        $this->assertInstanceOf(ReturnResponse::class, $response);
        $this->assertEquals($result, $response->parameters()['value']);
    }

    protected function createHandler(): Handler
    {
        return new FileInfoHandler($this->fileInfo->reveal());
    }
}
