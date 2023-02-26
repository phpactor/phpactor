<?php

namespace Phpactor\Tests\System\Extension\ClassMover\Command;

use Phpactor\Tests\System\SystemTestCase;

class ClassMoveCommandTest extends SystemTestCase
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
    public function testSmokeSuccess($command, array $fileMap = []): void
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
    }

    public function provideSmokeSuccess()
    {
        return [
            'Move file 1' => [
                'class:move lib/Badger/Carnivorous.php lib/Aardvark/Insectarian.php',
            ],
            'Move file 2' => [
                'class:move lib/Aardvark/Edentate.php lib/Foobar.php',
                [
                    'lib/Foobar.php' => true,
                    'lib/Aardvark/Edentate.php' => false,
                ],
            ],
            'Move file non-existing folder' => [
                'class:move lib/Aardvark/Edentate.php lib/Hello/World/Foobar.php',
                [
                    'lib/Hello/World/Foobar.php' => true,
                ],
            ],
            'Move file to folder' => [
                'class:move lib/Aardvark/Edentate.php lib/Hello/World/',
                [
                    'lib/Hello/World/Edentate.php' => true,
                ],
            ],
            'Move file force' => [
                'class:move lib/Aardvark/Edentate.php lib/Foobar.php --type=file',
            ],
            'Move folder 1' => [
                'class:move lib/Aardvark lib/Elephant',
                [
                    'lib/Aardvark' => false,
                    'lib/Elephant/Edentate.php' => true,
                ],
            ],
            'Move wildcard' => [
                'class:move "lib/*" lib/Foobar',
                [
                    'lib/Foobar/Aardvark' => true,
                    'lib/Foobar/Badger.php' => true,
                    'lib/Badger.php' => false,
                ],
            ],
            'Move class by name 1' => [
                'class:move "Animals\\Badger\\Carnivorous" "Animals\\Badger\\Vicious"',
            ],
            'Move class by name force' => [
                'class:move "Animals\\Badger\\Carnivorous" "Animals\\Badger\\Vicious" --type=class',
            ],
        ];
    }

    public function testOutdatedGitIndex(): void
    {
        rename($this->workspaceDir() . '/lib/Badger.php', $this->workspaceDir() . '/lib/Crow.php');
        $process = $this->phpactorFromStringArgs('class:move lib/Badger/Carnivorous.php lib/Aardvark/Insectarian.php');
        $this->assertSuccess($process);
    }

    public function testMovesRelatedFiles(): void
    {
        $this->workspace()->put('.phpactor.json', json_encode([
            'navigator.destinations' => [
                'source' => 'lib/<kernel>.php',
                'test' => 'lib/<kernel>Test.php'
            ]
        ]));
        $this->workspace()->put('lib/BadgerTest.php', '<?php namespace Animals; class BadgerTest {}');
        $process = $this->phpactorFromStringArgs('class:move lib/Badger.php lib/Fox.php --related');
        $this->assertSuccess($process);
        $this->assertFileExists($this->workspace()->path('/lib/FoxTest.php'));
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
            'Move class by name force file' => [
                'mv "Animals\\Badger\\Carnivorous" "Animals\\Badger\\Vicious" --type=file',
                null,
            ],
            'Move class by file force class' => [
                'class:move lib/Aardvark/Edentate.php lib/Foobar.php --type=class',
                null,
            ],
            'Move invalid type' => [
                'class:move lib/Aardvark/Edentate.php lib/Foobar.php --type=foobar',
                'Invalid type "foobar", must be one of: "auto", "file", "class"',
            ],
        ];
    }
}
