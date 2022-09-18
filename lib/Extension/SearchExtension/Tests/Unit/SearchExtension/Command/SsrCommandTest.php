<?php

namespace Phpactor\Extension\SearchExtension\Tests\Unit\SearchExtension\Command;

use Closure;
use Generator;
use Phpactor\TestUtils\PHPUnit\TestCase;
use Phpactor\TestUtils\Workspace;
use Symfony\Component\Process\Process;

class SsrCommandTest extends TestCase
{
    /**
     * @dataProvider provideSsr
     * @param array<string> $args
     */
    public function testCommand(array $args, Closure $assertion): void
    {
        $workspace = new Workspace(__DIR__ . '/../Workspace');
        $workspace->reset();
        $process = new Process(
            array_merge([
                __DIR__ . '/../../../../../../../bin/phpactor',
                'ssr'
            ], $args),
            $workspace->path()
        );
        $assertion($process);
    }

    public function provideSsr(): Generator
    {
        yield 'path and template' => [
            [
                '.',
                'class Foobar {}',
            ],
            function (Process $process): void {
                $process->mustRun();
                self::assertEquals(0, $process->getExitCode());
            }
        ];
        yield 'text filter' => [
            [
                '.',
                'class Foobar {}',
                '--text=A:Foobar',
            ],
            function (Process $process): void {
                $process->mustRun();
                self::assertEquals(0, $process->getExitCode());
            }
        ];
        yield 'text filter, no placeholder specified' => [
            [
                '.',
                'class Foobar {}',
                '--text=Foobar',
            ],
            function (Process $process): void {
                $exit = $process->run();
                self::assertEquals(255, $exit);
                self::assertStringContainsString('Invalid specification', $process->getErrorOutput());
            }
        ];
        yield 'type filter' => [
            [
                '.',
                'class Foobar {}',
                '--type=A:Foobar',
            ],
            function (Process $process): void {
                $process->mustRun();
                self::assertEquals(0, $process->getExitCode());
            }
        ];
        yield 'replace' => [
            [
                '.',
                'class Foobar {}',
                '--replace=A:Foobar',
            ],
            function (Process $process): void {
                $process->mustRun();
                self::assertEquals(0, $process->getExitCode());
            }
        ];
    }
}
