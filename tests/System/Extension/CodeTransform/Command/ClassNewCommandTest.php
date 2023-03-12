<?php

namespace Phpactor\Tests\System\Extension\CodeTransform\Command;

use Generator;
use Phpactor\Tests\System\SystemTestCase;

class ClassNewCommandTest extends SystemTestCase
{
    public function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
        $this->workspace()->put('.phpactor/templates/foobar/SourceCode.php.twig', 'Foobar');
        $this->workspace()->put(
            '.phpactor.json',
            <<<EOF
                {
                    "code_transform.class_new.variants": {
                        "foobar": "foobar"
                    }
                }
                EOF
        );
    }

    /**
     * Application level smoke tests
     *
     * @dataProvider provideNewClass
     */
    public function testNewClass(string $command, string $expected, string $expectedFile): void
    {
        $process = $this->phpactorFromStringArgs($command);
        $this->assertSuccess($process);

        $this->assertStringContainsString($expected, trim($process->getOutput()));
        $this->assertFileExists($this->workspaceDir() . $expectedFile);
    }

    /**
     * @return Generator<string,array{string,string,string}>
     */
    public function provideNewClass(): Generator
    {
        yield 'New class' => [
            'class:new lib/Badger/Teeth.php --no-interaction --force',
            <<<'EOT'
                src:lib/Badger/Teeth.php
                EOT
            , '/lib/Badger/Teeth.php'
        ];

        yield 'New class with variant' => [
            'class:new lib/Badger/Teeth.php --variant=foobar --no-interaction --force',
            <<<'EOT'
                src:lib/Badger/Teeth.php
                EOT
            , '/lib/Badger/Teeth.php'
        ];

        yield 'New class from FQN' => [
            'class:new "Animals\\Pigeon" --no-interaction',
            <<<'EOT'
                lib/Pigeon.php
                EOT
            , '/lib/Pigeon.php'
        ];
    }
}
