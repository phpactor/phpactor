<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Model;

use Amp\Process\Process;
use Amp\Promise;
use Phpactor\Amp\Process\ProcessBuilder;
use Phpactor\Extension\LanguageServerPhpCsFixer\Exception\PhpCsFixerError;
use Psr\Log\LoggerInterface;
use Composer\Semver\Comparator;

use function Amp\ByteStream\buffer;
use function Amp\call;
use function Amp\Promise\wait;

use Throwable;

class PhpCsFixerProcess
{
    public const EXIT_SOME_FILES_INVALID = 4;
    public const EXIT_FILES_NEEDS_FIXING = 8;

    /** @var string[] */
    private array $ignorePhpVersionArgs = ['--allow-unsupported-php-version=yes'];

    /**
     * @param array<string,string> $env
     */
    public function __construct(
        private string $binPath,
        private LoggerInterface $logger,
        private array $env = [],
        private ?string $configPath = null,
    ) {
    }

    /**
     * @param  string[] $options
     *
     * @return Promise<string>
     */
    public function fix(string $content, array $options = []): Promise
    {
        return call(function () use ($content, $options) {
            $this->ignorePhpVersion();

            if (false === array_search('--rules', $options, true) && null !== $this->configPath) {
                $options = array_merge($options, ['--config', $this->configPath]);
            }

            /** @var Process */
            $process = yield $this->run('fix', ...[
                ...$this->ignorePhpVersionArgs,
                ...$options,
                '-',
            ]);

            $stdin = $process->getStdin();
            $stdin->write($content);
            $stdin->end();

            $stdout = yield buffer($process->getStdout());
            $exitCode = yield $process->join();

            if (
                $exitCode !== 0
                && $exitCode !== self::EXIT_SOME_FILES_INVALID
                && $exitCode !== self::EXIT_FILES_NEEDS_FIXING
                && $exitCode !== (self::EXIT_SOME_FILES_INVALID | self::EXIT_FILES_NEEDS_FIXING)
            ) {
                throw new PhpCsFixerError(
                    $exitCode,
                    $process->getCommand(),
                    yield buffer($process->getStderr()),
                    $stdout,
                );
            }

            return $stdout;
        });
    }

    /**
     * @param string[] $options
     *
     * @return Promise<string>
     */
    public function describe(string $rule, array $options = []): Promise
    {
        return call(function () use ($rule, $options) {
            /** @var Process */
            $process = yield $this->run('describe', ...[...$options, $rule]);

            $stdout = yield buffer($process->getStdout());
            $exitCode = yield $process->join();

            if ($exitCode !== 0) {
                throw new PhpCsFixerError(
                    $exitCode,
                    $process->getCommand(),
                    yield buffer($process->getStderr()),
                    $stdout,
                );
            }

            return $stdout;
        });
    }

    /**
     * @return Promise<Process>
     */
    public function run(string ...$args): Promise
    {
        return call(function () use ($args) {
            $process = ProcessBuilder::create([PHP_BINARY, $this->binPath, ...$args])->mergeParentEnv()->env($this->env)->build();
            yield $process->start();

            $process->join()
                ->onResolve(function (?Throwable $error, $data) use ($process): void {
                    $this->logger->log(
                        $error ? 'warning' : 'debug',
                        sprintf(
                            'Executed %s, which exited with %s',
                            $process->getCommand(),
                            $data,
                        ),
                    );
                });

            return $process;
        });
    }

    private function ignorePhpVersion(): void
    {
        $version = $this->checkVersion();

        if (null === $version) {
            return;
        }

        $useNewerVersions = Comparator::greaterThanOrEqualTo($version, '3.89.2');

        if ($useNewerVersions) {
            unset($this->env['PHP_CS_FIXER_IGNORE_ENV']);
            return;
        }

        $this->ignorePhpVersionArgs = [];
        $this->env['PHP_CS_FIXER_IGNORE_ENV'] = '1';

        return;
    }

    private function checkVersion(): ?string
    {
        $versionQuery = wait((new self($this->binPath, $this->logger, []))->run('--version'));
        $stdout = wait(buffer($versionQuery->getStdout()));

        if (wait($versionQuery->join()) !== 0) {
            return null;
        }

        preg_match('/^PHP CS Fixer (\d+\.\d+\.\d+) /', $stdout, $version);

        return $version[1] ?? null;
    }
}
