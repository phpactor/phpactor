<?php

namespace Phpactor\Tests\System\Extension\ClassToFile\Rpc;

use Phpactor\Tests\System\SystemTestCase;

class FileInfoTest extends SystemTestCase
{
    public function setUp()
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    public function testReturnsFileInfo()
    {
        $stdin = json_encode([
            'action' => 'file_info',
            'parameters' => [
                'path' => 'lib/Badger/Carnivorous.php',
            ],
        ]);

        $process = $this->phpactor('rpc', $stdin);

        $response = json_decode($process->getOutput(), true);

        $this->assertSuccess($process);
        $this->assertEquals([
            'action' => 'return',
            'parameters' => [
                'value' => [
                    'class' => 'Animals\Badger\Carnivorous',
                    'class_name' => 'Carnivorous',
                    'class_namespace' => 'Animals\Badger',
                ]
            ],
        ], $response);
    }
}
