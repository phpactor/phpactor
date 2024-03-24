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
        $process = $this->makeEnvDumpProcess()->build();
        $pid = wait($process->start());
        $exitCode = wait($process->join());
        self::assertEquals(0, $exitCode);
    }

    public function testDoesNotMergeEnvByDefaultWhenEnvVarsPassed(): void
    {
        putenv('FOO='.self::PARENT_PROCESS_ENV_VAR);
        $process = $this->makeEnvDumpProcess()->env(['ENV'=> 'myenvvar'])->build();
        $pid = wait($process->start());
        /** @var string $out @phpstan-ignore-next-line */
        $out = wait(buffer($process->getStdout()));
        self::assertStringContainsString('myenvvar', $out);
        self::assertStringNotContainsString(self::PARENT_PROCESS_ENV_VAR, $out);
        $exitCode = wait($process->join());
        self::assertEquals(0, $exitCode);
        putenv('FOO');
    }

    public function testInheritsWhenInstructedToWhenEnvVarsPassed(): void
    {
        putenv('FOO='.self::PARENT_PROCESS_ENV_VAR);
        $process = $this->makeEnvDumpProcess()->env(['ENV'=> 'myenvvar'])->mergeParentEnv()->build();
        $pid = wait($process->start());
        /** @var string $out @phpstan-ignore-next-line */
        $out = wait(buffer($process->getStdout()));
        self::assertStringContainsString('myenvvar', $out);
        self::assertStringContainsString(self::PARENT_PROCESS_ENV_VAR, $out);
        $exitCode = wait($process->join());
        self::assertEquals(0, $exitCode);
        putenv('FOO');
    }

    private function makeEnvDumpProcess(): ProcessBuilder
    {
        // Short script that just prints its environment variables,
        // so that the tests can verify what it received.
        return ProcessBuilder::create([PHP_BINARY, '-r', 'var_dump(getenv());']);
    }
}
