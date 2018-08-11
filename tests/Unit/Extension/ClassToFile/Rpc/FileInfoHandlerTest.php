<?php

namespace Phpactor\Tests\Unit\Extension\ClassToFile\Rpc;

use Phpactor\Extension\ClassToFile\Application\FileInfo;
use Phpactor\Extension\ClassToFile\Rpc\FileInfoHandler;
use Phpactor\Extension\Rpc\Response\ReturnResponse;
use PHPUnit\Framework\TestCase;

class FileInfoHandlerTest extends TestCase
{
    public function testReturnsAResponseWithAFileInfo()
    {
        $path =  'src/Controller/BlogController.php';
        $result = [
            'class' => 'App\Controller\BlogController',
            'class_name' => 'BlogController',
            'class_namespace' => 'App\Controller',
        ];

        $fileInfo = $this->prophesize(FileInfo::class);
        $fileInfo->infoForFile($path)->willReturn($result);

        $handler = new FileInfoHandler($fileInfo->reveal());

        $response = $handler->handle(['path' => $path]);

        $this->assertInstanceOf(ReturnResponse::class, $response);
        $this->assertEquals($result, $response->parameters()['value']);
    }
}
