<?php

namespace Phpactor\Tests\Integration\Console\Command;

use Phpactor\Tests\Integration\SystemTestCase;

class ClassInflectCommandTest extends SystemTestCase
{
    public function setUp()
    {
        $this->initWorkspace();
        $this->loadProject('Animals');
    }

    /**
     * Application level smoke tests
     *
     * @dataProvider provideInflectClass
     */
    public function testInflectClass($command, $expectedFilePath, $expectedContents)
    {
        $process = $this->phpactor($command);
        $this->assertSuccess($process);

        $expectedFilePath = $this->workspaceDir() . '/' . $expectedFilePath;
        $this->assertFileExists($expectedFilePath);
        $this->assertContains($expectedContents, file_get_contents($expectedFilePath));
    }

    public function provideInflectClass()
    {
        return [
            'Inflect class' => [
                'class:inflect lib/Badger/Carnivorous.php lib/Badger/Api/CarnivorousInterface.php interface',
                'lib/Badger/Api/CarnivorousInterface.php',
                <<<'EOT'
interface CarnivorousInterface
EOT
            ],
        ];
    }

    /**
     * @testdox It does not overwrite existing file unless forced.
     */
    public function testInflectClassExistingAndForce()
    {
        $filePath =  'lib/Badger/Carnivorous.php';
        $process = $this->phpactor('class:inflect '.$filePath. ' ' . $filePath . ' interface');
        $this->assertSuccess($process);
        $this->assertContains('exists:1', $process->getOutput());
        $this->assertNotContains('interface', file_get_contents($filePath));

        $process = $this->phpactor('class:inflect '.$filePath. ' ' . $filePath . ' interface --force');
        $this->assertContains('interface', file_get_contents($filePath));
    }
}

