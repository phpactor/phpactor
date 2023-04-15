<?php

namespace Phpactor\Amp\Tests\Process;

use PHPUnit\Framework\TestCase;
use Phpactor\Amp\Process\ProcessBuilder;
use function Amp\ByteStream\buffer;
use function Amp\Promise\wait;

class ProcessBuilderTest extends TestCase
{
    const PARENT_PROCESS_ENV_VAR = 'thisistheparentprocess';

    public function testBuildProcess(): void
    {
        $process = ProcessBuilder::create(['export'])->build();
        $pid = wait($process->start());
        $exitCode = wait($process->join());
        self::assertEquals(0, $exitCode);
    }

    public function testDoesNotMergeEnvByDefaultWhenEnvVarsPassed(): void
    {
        putenv('FOO='.self::PARENT_PROCESS_ENV_VAR);
        $process = ProcessBuilder::create(['export'])->env(['ENV'=> 'myenvvar'])->build();
        $pid = wait($process->start());
        $exitCode = wait($process->join());
        self::assertEquals(0, $exitCode);
        /** @var string $out @phpstan-ignore-next-line */
        $out = wait(buffer($process->getStdout()));
        self::assertStringContainsString('myenvvar', $out);
        self::assertStringNotContainsString(self::PARENT_PROCESS_ENV_VAR, $out);
        putenv('FOO');
    }

    public function testInheritsWhenInstructedToWhenEnvVarsPassed(): void
    {
        putenv('FOO='.self::PARENT_PROCESS_ENV_VAR);
        $process = ProcessBuilder::create(['export'])->env(['ENV'=> 'myenvvar'])->mergeParentEnv()->build();
        $pid = wait($process->start());
        $exitCode = wait($process->join());
        self::assertEquals(0, $exitCode);
        /** @var string $out @phpstan-ignore-next-line */
        $out = wait(buffer($process->getStdout()));
        self::assertStringContainsString('myenvvar', $out);
        self::assertStringContainsString(self::PARENT_PROCESS_ENV_VAR, $out);
        putenv('FOO');
    }
}
