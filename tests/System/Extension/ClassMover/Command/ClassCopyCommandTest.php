<?php

namespace Phpactor\Tests\System\Extension\ClassMover\Command;

use Phpactor\Tests\System\SystemTestCase;

class ClassCopyCommandTest extends SystemTestCase
{
    public function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    /**
     * Application level smoke tests
     *
     * @dataProvider provideSmokeSuccess
     */
    public function testSmokeSuccess($command, array $fileMap = [], array $contentExpectations = []): void
    {
        $process = $this->phpactorFromStringArgs($command);
        $this->assertSuccess($process);

        foreach ($fileMap as $filePath => $shouldExist) {
            $exists = file_exists($this->workspaceDir() . '/' . $filePath);

            if ($shouldExist) {
                $this->assertTrue($exists);
                continue;
            }

            $this->assertFalse($exists);
        }

        foreach ($contentExpectations as $filePath => $contentExpectation) {
            $path = $this->workspaceDir() . '/' . $filePath;
            $contents = file_get_contents($path);
            $this->assertStringContainsString($contentExpectation, $contents);
        }
    }

    public function provideSmokeSuccess()
    {
        return [
            'Copy file 1' => [
                'class:copy lib/Badger/Carnivorous.php lib/Aardvark/Insectarian.php',
                [],
                [
                    'lib/Aardvark/Insectarian.php' => 'class Insectarian',
                ]
            ],
            'Copy file 2' => [
                'class:copy lib/Aardvark/Edentate.php lib/Foobar.php',
                [
                    'lib/Foobar.php' => true,
                    'lib/Aardvark/Edentate.php' => true,
                ],
            ],
            'Copy file non-existing folder' => [
                'class:copy lib/Aardvark/Edentate.php lib/Hello/World/Foobar.php',
                [
                    'lib/Hello/World/Foobar.php' => true,
                ],
            ],
            'Copy file to folder' => [
                'class:copy lib/Aardvark/Edentate.php lib/Hello/World/',
                [
                    'lib/Hello/World/Edentate.php' => true,
                ],
            ],
            'Copy file force' => [
                'class:copy lib/Aardvark/Edentate.php lib/Foobar.php --type=file',
            ],
            'Copy folder 1' => [
                'class:copy lib/Aardvark lib/Elephant',
                [
                    'lib/Aardvark' => true,
                    'lib/Elephant/Edentate.php' => true,
                ],
            ],
            'Copy wildcard' => [
                'class:copy "lib/*" lib/Foobar',
                [
                    'lib/Foobar/Aardvark' => true,
                    'lib/Foobar/Badger.php' => true,
                    'lib/Badger.php' => true,
                ],
            ],
            'Copy class by name 1' => [
                'class:copy "Animals\\Badger\\Carnivorous" "Animals\\Badger\\Vicious"',
            ],
            'Copy class by name force' => [
                'class:copy "Animals\\Badger\\Carnivorous" "Animals\\Badger\\Vicious" --type=class',
            ],
        ];
    }

    /**
     * Application level failures
     *
     * @dataProvider provideSmokeFailure
     */
    public function testSmokeFailure($command, $expectedMessage = null): void
    {
        $process = $this->phpactorFromStringArgs($command);
        $this->assertFailure($process, $expectedMessage);
    }

    public function provideSmokeFailure()
    {
        return [
            'Copy class by name force file' => [
                'mv "Animals\\Badger\\Carnivorous" "Animals\\Badger\\Vicious" --type=file',
                null,
            ],
            'Copy class by file force class' => [
                'class:copy lib/Aardvark/Edentate.php lib/Foobar.php --type=class',
                null,
            ],
            'Copy invalid type' => [
                'class:copy lib/Aardvark/Edentate.php lib/Foobar.php --type=foobar',
                'Invalid type "foobar", must be one of: "auto", "file", "class"',
            ],
            'Copy non-existing' => [
                'class:copy lib/Aardvark/Blah.php lib/Foobar.php',
                'does not exist',
            ],
        ];
    }
}
