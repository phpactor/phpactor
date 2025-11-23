<?php

namespace Phpactor\Tests\System\Extension\CodeTransform\Command;

use PHPUnit\Framework\Attributes\DataProvider;
use Generator;
use Phpactor\Tests\System\SystemTestCase;

class ClassTransformCommandTest extends SystemTestCase
{
    public function setUp(): void
    {
        $this->workspace()->reset();
        $this->loadProject('Animals');
        file_put_contents(
            $this->workspace()->path('lib/Foobar.php'),
            <<<'EOT'
                <?php

                class Foobar implements Countable
                {
                }
                EOT
        );
    }

    /**
     * Application level smoke tests
     */
    #[DataProvider('provideSmokeSuccess')]
    public function testSmokeSuccess(string $command, string $expectedOutput, bool $error = false): void
    {
        $process = $this->phpactorFromStringArgs($command);

        if ($error) {
            $this->assertStringContainsString($expectedOutput, $process->getErrorOutput());
            return;
        }

        $this->assertSuccess($process);
        $this->assertStringContainsString($expectedOutput, $process->getOutput());
    }

    /**
     * @return Generator<string,array{string,string}|array{string,string,bool}>
     */
    public static function provideSmokeSuccess(): Generator
    {
        yield 'No arguments' => [
            'class:transform lib/Foobar.php',
            '0 files affected',
        ];

        yield 'Implement contracts' => [
            'class:transform lib/Foobar.php --transform=implement_contracts',
            '1 files affected',
        ];

        yield 'Glob' => [
            'class:transform "lib/*.php" --transform=implement_contracts',
            '1 files affected',
        ];

        yield 'Dry run' => [
            'class:transform "lib/**/*.php" --dry-run --transform=implement_contracts',
            '1 files affected (dry run)',
        ];

        yield 'Diff' => [
            'class:transform "lib/*.php" --diff --transform=implement_contracts',
            'public function count',
        ];

        yield 'Non-existing file' => [
            'class:transform "lib/BarNotExisting.php" --diff --transform=implement_contracts',
            'does not exist',
            true
        ];
    }
}
