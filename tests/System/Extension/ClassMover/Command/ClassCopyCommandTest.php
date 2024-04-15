<?php

namespace Phpactor\Tests\System\Extension\ClassMover\Command;

use Generator;
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
    public function testSmokeSuccess(string $command, array $fileMap = [], array $contentExpectations = []): void
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

    public function provideSmokeSuccess(): Generator
    {
        yield 'Copy file 1' => [
            'class:copy lib/Badger/Carnivorous.php lib/Aardvark/Insectarian.php',
            [],
            [
                'lib/Aardvark/Insectarian.php' => 'class Insectarian',
            ]
        ];

        yield 'Copy file 2' => [
            'class:copy lib/Aardvark/Edentate.php lib/Foobar.php',
            [
                'lib/Foobar.php' => true,
                'lib/Aardvark/Edentate.php' => true,
            ],
        ];
        yield 'Copy file non-existing folder' => [
            'class:copy lib/Aardvark/Edentate.php lib/Hello/World/Foobar.php',
            [
                'lib/Hello/World/Foobar.php' => true,
            ],
        ];
        yield 'Copy file to folder' => [
            'class:copy lib/Aardvark/Edentate.php lib/Hello/World/',
            [
                'lib/Hello/World/Edentate.php' => true,
            ],
        ];
        yield 'Copy file force' => [
            'class:copy lib/Aardvark/Edentate.php lib/Foobar.php --type=file',
            [],
            [],
        ];
        yield 'Copy folder 1' => [
            'class:copy lib/Aardvark lib/Elephant',
            [
                'lib/Aardvark' => true,
                'lib/Elephant/Edentate.php' => true,
            ],
        ];
        yield 'Copy wildcard' => [
            'class:copy "lib/*" lib/Foobar',
            [
                'lib/Foobar/Aardvark' => true,
                'lib/Foobar/Badger.php' => true,
                'lib/Badger.php' => true,
            ],
        ];
        yield 'Copy class by name 1' => [
            'class:copy "Animals\\Badger\\Carnivorous" "Animals\\Badger\\Vicious"',
            [],
            [],
        ];
        yield 'Copy class by name force' => [
            'class:copy "Animals\\Badger\\Carnivorous" "Animals\\Badger\\Vicious" --type=class',
            [],
            [],
        ];
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
        yield 'Copy class by name force file' => [
            'mv "Animals\\Badger\\Carnivorous" "Animals\\Badger\\Vicious" --type=file',
            null,
        ];
        yield 'Copy class by file force class' => [
            'class:copy lib/Aardvark/Edentate.php lib/Foobar.php --type=class',
            null,
        ];
        yield 'Copy invalid type' => [
            'class:copy lib/Aardvark/Edentate.php lib/Foobar.php --type=foobar',
            'Invalid type "foobar", must be one of: "auto", "file", "class"',
        ];
        yield 'Copy non-existing' => [
            'class:copy lib/Aardvark/Blah.php lib/Foobar.php',
            'does not exist',
        ];
    }
}
