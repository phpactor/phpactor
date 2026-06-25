<?php

namespace Phpactor\Extension\LanguageServerMago\Tests\Model;

use Amp\CancellationTokenSource;
use Amp\Loop;
use Amp\NullCancellationToken;
use Phpactor\Extension\LanguageServerMago\Model\MagoConfig;
use Phpactor\Extension\LanguageServerMago\Model\MagoProcess;
use Phpactor\Extension\LanguageServerMago\Tests\IntegrationTestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\Log\Test\TestLogger;
use function Amp\Promise\wait;

class MagoProcessTest extends IntegrationTestCase
{
    private const DOCUMENT_TEXT = "<?php\n\$x = 1;\n";
    private const RELATIVE_PATH = 'src/A.php';

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->workspace()->mkdir('capture');
    }

    public function testRunsBinaryDirectlyAndParsesOutput(): void
    {
        $this->fakeMago($this->issuesJson());

        $diagnostics = wait($this->process()->analyse(
            'analyze',
            'mago',
            self::RELATIVE_PATH,
            'file:///workspace/src/A.php',
            self::DOCUMENT_TEXT,
            new NullCancellationToken(),
        ));

        self::assertCount(1, $diagnostics);
        self::assertSame('invalid-argument', $diagnostics[0]->code);
        self::assertSame('mago', $diagnostics[0]->source);

        // The subcommand precedes its flags, the reporting format is JSON, and
        // the workspace-relative path is passed to --stdin-input.
        self::assertSame(
            'analyze --reporting-format=json --stdin-input src/A.php',
            trim($this->workspace()->getContents('capture/args')),
        );
        // The document buffer is piped on stdin.
        self::assertSame(self::DOCUMENT_TEXT, $this->workspace()->getContents('capture/stdin'));
        // The process runs in the project root.
        self::assertSame(
            realpath($this->workspace()->path()),
            realpath(trim($this->workspace()->getContents('capture/cwd'))),
        );
    }

    public function testPassesGlobalConfigBeforeSubcommand(): void
    {
        $this->fakeMago('');

        wait($this->process('/etc/mago.toml')->analyse(
            'lint',
            'mago-lint',
            self::RELATIVE_PATH,
            'file:///workspace/src/A.php',
            self::DOCUMENT_TEXT,
            new NullCancellationToken(),
        ));

        self::assertSame(
            '--config /etc/mago.toml lint --reporting-format=json --stdin-input src/A.php',
            trim($this->workspace()->getContents('capture/args')),
        );
    }

    public function testEmptyOutputYieldsNoDiagnostics(): void
    {
        $this->fakeMago('');

        $diagnostics = wait($this->process()->analyse(
            'analyze',
            'mago',
            self::RELATIVE_PATH,
            'file:///workspace/src/A.php',
            self::DOCUMENT_TEXT,
            new NullCancellationToken(),
        ));

        self::assertSame([], $diagnostics);
    }

    public function testInvalidOutputIsLoggedAndYieldsNoDiagnostics(): void
    {
        $this->fakeMago('this is not json');
        $logger = new TestLogger();

        $diagnostics = wait($this->process(null, $logger)->analyse(
            'analyze',
            'mago',
            self::RELATIVE_PATH,
            'file:///workspace/src/A.php',
            self::DOCUMENT_TEXT,
            new NullCancellationToken(),
        ));

        self::assertSame([], $diagnostics);
        self::assertTrue($logger->hasErrorRecords());
    }

    public function testTimeoutKillsProcessAndYieldsNoDiagnostics(): void
    {
        $this->fakeMago($this->issuesJson(), sleepSeconds: 5);
        $logger = new TestLogger();

        $diagnostics = wait($this->process(null, $logger, timeout: 200)->analyse(
            'analyze',
            'mago',
            self::RELATIVE_PATH,
            'file:///workspace/src/A.php',
            self::DOCUMENT_TEXT,
            new NullCancellationToken(),
        ));

        self::assertSame([], $diagnostics);
        self::assertTrue($logger->hasErrorRecords());
    }

    public function testCancellationKillsProcessAndYieldsNoDiagnostics(): void
    {
        $this->fakeMago($this->issuesJson(), sleepSeconds: 5);
        $source = new CancellationTokenSource();

        $promise = $this->process(null, null, timeout: 10000)->analyse(
            'analyze',
            'mago',
            self::RELATIVE_PATH,
            'file:///workspace/src/A.php',
            self::DOCUMENT_TEXT,
            $source->getToken(),
        );
        Loop::delay(100, function () use ($source): void {
            $source->cancel();
        });

        self::assertSame([], wait($promise));
    }

    private function process(?string $config = null, ?LoggerInterface $logger = null, int $timeout = 5000): MagoProcess
    {
        return new MagoProcess(
            $this->workspace()->path(),
            new MagoConfig($this->workspace()->path('mago'), $timeout, $config),
            $logger ?? new NullLogger(),
        );
    }

    private function fakeMago(string $output, int $sleepSeconds = 0): void
    {
        $capture = $this->workspace()->path('capture');
        $this->workspace()->put('capture/output', $output);
        $sleep = $sleepSeconds > 0 ? "sleep $sleepSeconds\n" : '';
        $script = <<<BASH
            #!/usr/bin/env bash
            printf '%s' "\$*" > "$capture/args"
            pwd > "$capture/cwd"
            cat > "$capture/stdin"
            $sleep
            cat "$capture/output"
            BASH;
        $this->workspace()->put('mago', $script);
        chmod($this->workspace()->path('mago'), 0755);
    }

    private function issuesJson(): string
    {
        return (string)json_encode([
            'issues' => [
                [
                    'level' => 'Error',
                    'code' => 'invalid-argument',
                    'message' => 'bad',
                    'annotations' => [
                        [
                            'kind' => 'Primary',
                            'span' => [
                                'file_id' => ['name' => self::RELATIVE_PATH],
                                'start' => ['offset' => 6, 'line' => 1],
                                'end' => ['offset' => 8, 'line' => 1],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
