<?php

namespace Phpactor\Tests\System\Extension\ClassMover\Command;

use Generator;
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
     * @param array<string, bool> $fileMap
     *
     * @dataProvider provideSmokeSuccess
     */
    public function testSmokeSuccess(string $command, array $fileMap): void
    {
        $process = $this->phpactorFromStringArgs($command);
        $this->assertSuccess($process);

        foreach ($fileMap as $filePath => $shouldExist) {
            $absFilePath = $this->workspaceDir() . '/' . $filePath;

            if ($shouldExist) {
                $this->assertFileExists($absFilePath);
                continue;
            }

            $this->assertFileDoesNotExist($absFilePath);
        }
    }

    /**
     * @return Generator<string, array{string,array<string, bool>}>
     */
    public function provideSmokeSuccess(): Generator
    {
        yield 'Move file 1' => [
            'class:move lib/Badger/Carnivorous.php lib/Aardvark/Insectarian.php',
            [
                'lib/Badger/Carnivorous.php' => false,
                'lib/Aardvark/Insectarian.php' => true,
            ],
        ];
        yield 'Move file 2' => [
            'class:move lib/Aardvark/Edentate.php lib/Foobar.php',
            [
                'lib/Foobar.php' => true,
                'lib/Aardvark/Edentate.php' => false,
            ],
        ];
        yield 'Move file non-existing folder' => [
            'class:move lib/Aardvark/Edentate.php lib/Hello/World/Foobar.php',
            [
                'lib/Hello/World/Foobar.php' => true,
            ],
        ];
        yield 'Move file to folder' => [
            'class:move lib/Aardvark/Edentate.php lib/Hello/World/',
            [
                'lib/Hello/World/Edentate.php' => true,
            ],
        ];
        yield 'Move file force' => [
            'class:move lib/Aardvark/Edentate.php lib/Foobar.php --type=file',
            [],
        ];
        yield'Move folder 1' => [
            'class:move lib/Aardvark lib/Elephant',
            [
                'lib/Aardvark' => false,
                'lib/Elephant/Edentate.php' => true,
            ],
        ];
        yield 'Move wildcard' => [
            'class:move "lib/*" lib/Foobar',
            [
                'lib/Foobar/Aardvark' => true,
                'lib/Foobar/Badger.php' => true,
                'lib/Badger.php' => false,
            ],
        ];
        yield 'Move class by name 1' => [
            'class:move "Animals\\Badger\\Carnivorous" "Animals\\Badger\\Vicious"',
            [],
        ];
        yield 'Move class by name force' => [
            'class:move "Animals\\Badger\\Carnivorous" "Animals\\Badger\\Vicious" --type=class',
            [],
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
        ], JSON_THROW_ON_ERROR));
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
    public function testSmokeFailure(string $command, ?string $expectedMessage = null): void
    {
        $process = $this->phpactorFromStringArgs($command);
        $this->assertFailure($process, $expectedMessage);
    }

    /**
     * @return Generator<string, array{string, string|null}>
     */
    public function provideSmokeFailure(): Generator
    {
        yield 'Move class by name force file' => [
            'mv "Animals\\Badger\\Carnivorous" "Animals\\Badger\\Vicious" --type=file',
            null,
        ];

        yield 'Move class by file force class' => [
            'class:move lib/Aardvark/Edentate.php lib/Foobar.php --type=class',
            null,
        ];

        yield 'Move invalid type' => [
            'class:move lib/Aardvark/Edentate.php lib/Foobar.php --type=foobar',
            'Invalid type "foobar", must be one of: "auto", "file", "class"',
        ];
    }
}
