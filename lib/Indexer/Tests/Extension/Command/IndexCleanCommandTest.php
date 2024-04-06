<?php

namespace Phpactor\Indexer\Tests\Extension\Command;

use Generator;
use Phpactor\Indexer\Extension\Command\IndexCleanCommand;
use Phpactor\Indexer\Tests\IntegrationTestCase;
use Symfony\Component\Process\Process;

class IndexCleanCommandTest extends IntegrationTestCase
{
    private const CONSOLE_PATH = __DIR__ . '/../../bin/console';

    /**
     * @dataProvider provideAllIndexClean
     * @param array<string> $command
     */
    public function testCleanIndexWithAllInput(array $command, ?string $input): void
    {
        $this->initProject();
        self::assertFalse($this->workspace()->exists('cache'));

        $process = new Process([PHP_BINARY, ...$command], $this->workspace()->path(), null, $input);
        $process->mustRun();

        self::assertEquals(0, $process->getExitCode());
        self::assertFalse($this->workspace()->exists('project'));
        self::assertFalse($this->workspace()->exists('vendor'));
    }

    /**
     * @return Generator<string,array<int,mixed>>
     */
    public function provideAllIndexClean(): Generator
    {
        yield 'interactive version' => [
            [ self::CONSOLE_PATH, 'index:clean'],
            IndexCleanCommand::OPT_CLEAN_ALL
        ];
        yield 'non-interactive version' => [
            [ self::CONSOLE_PATH, 'index:clean', IndexCleanCommand::OPT_CLEAN_ALL, '--no-interaction'],
            null
        ];
        yield 'cleaning index 1 and 2' => [
            [self::CONSOLE_PATH, 'index:clean'],
            "1\n1"
        ];
        yield 'cleaning multiple indexes non-interactive' => [
            [self::CONSOLE_PATH, 'index:clean', 'project','vendor', '--no-interaction'],
            null
        ];
    }

    /**
     * @dataProvider provideCleanSpecificIndex
     * @param array<string> $command
     */
    public function testCleanIndexWithSpecificInput(array $command, ?string $input): void
    {
        $this->initProject();

        $process = new Process([PHP_BINARY, ...$command], $this->workspace()->path(), null, $input);
        $process->mustRun();

        self::assertEquals(0, $process->getExitCode());
        self::assertFalse($this->workspace()->exists('project'));
        self::assertTrue($this->workspace()->exists('vendor'));
    }

    /**
     * @return Generator<string,array<int,mixed>>
     */
    public function provideCleanSpecificIndex(): Generator
    {
        yield 'interactive version' => [
            [ self::CONSOLE_PATH, 'index:clean'],
            '1'
        ];
        yield 'non-interactive version' => [
            [ self::CONSOLE_PATH, 'index:clean', 'project', '--no-interaction'],
            null
        ];
        yield 'non-interactive version with index name' => [
            [ self::CONSOLE_PATH, 'index:clean', 'project', '--no-interaction'],
            null
        ];
    }

    /**
     * @dataProvider provideDoNotRemoveAnything
     *
     * @param array<string> $arguments
     */
    public function testCleanDoesNotRemoveIndexWithoutInput(array $arguments): void
    {
        $this->initProject();

        $process = new Process([PHP_BINARY, ...$arguments], $this->workspace()->path(), null, null);
        $process->mustRun();

        self::assertEquals(0, $process->getExitCode());
        self::assertTrue($this->workspace()->exists('project'));
        self::assertTrue($this->workspace()->exists('vendor'));
    }

    /**
     * @return Generator<string,array<int,array<int,string>>>
     */
    public function provideDoNotRemoveAnything(): Generator
    {
        yield 'it deletes nothing on empty input' => [
            [ self::CONSOLE_PATH, 'index:clean'],
        ];
        yield 'it deletes nothing on no interactive' => [
            [ self::CONSOLE_PATH, 'index:clean', '--no-interaction'],
        ];
    }
}
