<?php

namespace Phpactor\Indexer\Tests\Extension\Command;

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

        $process = new Process($command, $this->workspace()->path(), null, $input);
        $process->mustRun();

        self::assertEquals(0, $process->getExitCode());
        self::assertFalse($this->workspace()->exists('project'));
        self::assertFalse($this->workspace()->exists('vendor'));
    }

    /**
     * @return array<string,array<int,mixed>>
     */
    public function provideAllIndexClean(): array
    {
        return [
            'interactive version' => [
                [ self::CONSOLE_PATH, 'index:clean'],
                IndexCleanCommand::CLEAN_ALL
            ],
            'non-interactive version' => [
                [ self::CONSOLE_PATH, 'index:clean', IndexCleanCommand::CLEAN_ALL, '--no-interaction'],
                null
            ],
            'cleaning index 1 and 2' => [
                [self::CONSOLE_PATH, 'index:clean'],
                '1,2'
            ],
            'cleaning index 1 and 2 non interactively' => [
                [self::CONSOLE_PATH, 'index:clean', '1,2', '--no-interaction'],
                null
            ]

        ];
    }

    /**
     * @dataProvider provideCleanSpecificIndex
     * @param array<string> $command
     */
    public function testCleanIndexWithSpecificInput(array $command, ?string $input): void
    {
        $this->initProject();

        $process = new Process($command, $this->workspace()->path(), null, $input);
        $process->mustRun();

        self::assertEquals(0, $process->getExitCode());
        self::assertFalse($this->workspace()->exists('project'));
        self::assertTrue($this->workspace()->exists('vendor'));
    }

    /**
     * @return array<string,array<int,mixed>>
     */
    public function provideCleanSpecificIndex(): array
    {
        return [
            'interactive version' => [
                [ self::CONSOLE_PATH, 'index:clean'],
                '1'
            ],
            'non-interactive version' => [
                [ self::CONSOLE_PATH, 'index:clean', '1', '--no-interaction'],
                null
            ],
            'non-interactive version with index name' => [
                [ self::CONSOLE_PATH, 'index:clean', 'project', '--no-interaction'],
                null
            ],
        ];
    }

    public function testCleanDoesNotRemoveIndexIfShellIsNotInteractive(): void
    {
        $this->initProject();

        $process = new Process(
            [ self::CONSOLE_PATH, 'index:clean', '--no-interaction'],
            $this->workspace()->path(),
        );
        $process->mustRun();

        self::assertEquals(0, $process->getExitCode());
        self::assertTrue($this->workspace()->exists('project'));
        self::assertTrue($this->workspace()->exists('vendor'));
    }
}
