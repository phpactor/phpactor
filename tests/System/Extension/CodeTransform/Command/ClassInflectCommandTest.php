<?php

namespace Phpactor\Tests\System\Extension\CodeTransform\Command;

use Generator;
use Phpactor\Tests\System\SystemTestCase;

class ClassInflectCommandTest extends SystemTestCase
{
    public function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
    }

    /**
     * Application level smoke tests
     *
     * @dataProvider provideInflectClass
     */
    public function testInflectClass(string $command, string $expectedFilePath, string $expectedContents): void
    {
        $process = $this->phpactorFromStringArgs($command);
        $this->assertSuccess($process);

        $expectedFilePath = $this->workspaceDir() . '/' . $expectedFilePath;
        $this->assertSuccess($process);
        $this->assertFileExists($expectedFilePath);
        $this->assertStringContainsString($expectedContents, (string) file_get_contents($expectedFilePath));
    }

    /**
     * @return Generator<string, array{string, string, string}>
     */
    public function provideInflectClass(): Generator
    {
        yield 'Glob' => [
            'class:inflect "lib/Badger/*.php" lib/Badger/Api interface',
            'lib/Badger/Api/Carnivorous.php',
            <<<'EOT'
                interface Carnivorous
                EOT
        ];

        yield 'Glob with directories' => [
            'class:inflect "lib/*" lib/Api interface',
            'lib/Api/Badger.php',
            <<<'EOT'
                interface Badger
                EOT
        ];

        yield 'Inflect class' => [
            'class:inflect lib/Badger/Carnivorous.php lib/Badger/Api/CarnivorousInterface.php interface',
            'lib/Badger/Api/CarnivorousInterface.php',
            <<<'EOT'
                interface CarnivorousInterface
                EOT
        ];
    }

    /**
     * @testdox It does not overwrite existing file unless forced.
     */
    public function testInflectClassExistingAndForce(): void
    {
        $filePath =  'lib/Badger/Carnivorous.php';
        $process = $this->phpactorFromStringArgs('class:inflect '.$filePath. ' ' . $filePath . ' interface --no-interaction');
        $this->assertSuccess($process);
        $this->assertStringContainsString('exists:true', $process->getOutput());
        $this->assertStringNotContainsString('interface', (string) file_get_contents($filePath));

        $process = $this->phpactorFromStringArgs('class:inflect '.$filePath. ' ' . $filePath . ' interface --force');
        $this->assertStringContainsString('interface', (string) file_get_contents($filePath));
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
     * @return Generator<string,array{string,string}>
     */
    public function provideSmokeFailure(): Generator
    {
        yield 'non-existing' => [
            'class:inflect lib/Badger/BooNotExist.php lib/Badger/Api/CarnivorousInterface.php interface',
            'does not exist',
        ];
    }
}
