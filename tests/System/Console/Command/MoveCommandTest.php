<?php

namespace Phpactor\Tests\System\Console\Command;

use Phpactor\Tests\System\SystemTestCase;

class MoveCommandTest extends SystemTestCase
{
    public function setUp()
    {
        $this->initWorkspace();
        $this->loadProject('Animals');
    }

    /**
     * Application level smoke tests
     *
     * @dataProvider provideSmokeSuccess
     */
    public function testSmokeSuccess($command, array $fileMap = [])
    {
        $process = $this->phpactor($command);
        $this->assertSuccess($process);

        foreach ($fileMap as $filePath => $shouldExist) {
            $exists = file_exists($this->workspaceDir() . '/' . $filePath);

            if ($shouldExist) {
                $this->assertTrue($exists);
                continue;
            }

            $this->assertFalse($exists);
        }
    }

    public function provideSmokeSuccess()
    {
        return [
            'Move file 1' => [
                'mv lib/Badger/Carnivorous.php lib/Aardvark/Insectarian.php',
            ],
            'Move file 2' => [
                'mv lib/Aardvark/Edentate.php lib/Foobar.php',
                [
                    'lib/Foobar.php' => true,
                    'lib/Aardvark/Edentate.php' => false,
                ],
            ],
            'Move file non-existing folder' => [
                'mv lib/Aardvark/Edentate.php lib/Hello/World/Foobar.php',
                [
                    'lib/Hello/World/Foobar.php' => true,
                ],
            ],
            'Move file force' => [
                'mv lib/Aardvark/Edentate.php lib/Foobar.php --type=file',
            ],
            'Move folder 1' => [
                'mv lib/Aardvark lib/Elephant',
                [
                    'lib/Aardvark' => false,
                    'lib/Elephant/Edentate.php' => true,
                ],
            ],
            'Move class by name 1' => [
                'mv "Animals\\Badger\\Carnivorous" "Animals\\Badger\\Vicious"',
            ],
            'Move class by name force' => [
                'mv "Animals\\Badger\\Carnivorous" "Animals\\Badger\\Vicious" --type=class',
            ],
        ];
    }

    /**
     * Application level failures
     *
     * @dataProvider provideSmokeFailure
     */
    public function testSmokeFailure($command, $expectedMessage = null)
    {
        $process = $this->phpactor($command);
        $this->assertFailure($process, $expectedMessage);
    }

    public function provideSmokeFailure()
    {
        return [
            'Move class by name force file' => [
                'mv "Animals\\Badger\\Carnivorous" "Animals\\Badger\\Vicious" --type=file',
                null,
            ],
            'Move class by file force class' => [
                'mv lib/Aardvark/Edentate.php lib/Foobar.php --type=class',
                null,
            ],
            'Move invalid type' => [
                'mv lib/Aardvark/Edentate.php lib/Foobar.php --type=foobar',
                'Invalid type "foobar", must be one of: "auto", "file", "class"',
            ],
        ];
    }
}
