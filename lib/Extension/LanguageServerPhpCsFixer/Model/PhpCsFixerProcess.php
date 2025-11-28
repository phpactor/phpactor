<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Model;

use Amp\Process\Process;
use Amp\Promise;
use Phpactor\Amp\Process\ProcessBuilder;
use Phpactor\Extension\LanguageServerPhpCsFixer\Exception\PhpCsFixerError;
use Phpactor\VersionResolver\SemVersion;
use Phpactor\VersionResolver\SemVersionResolver;
use Psr\Log\LoggerInterface;

use function Amp\ByteStream\buffer;
use function Amp\call;

use Throwable;

class PhpCsFixerProcess
{
    public const EXIT_SOME_FILES_INVALID = 4;
    public const EXIT_FILES_NEEDS_FIXING = 8;

    /**
     * @param array<string,string> $env
     */
    public function __construct(
        private string $binPath,
        private LoggerInterface $logger,
        private ?SemVersionResolver $versionResolver = null,
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
            $version = yield $this->versionResolver?->resolve();

            if (false === array_search('--rules', $options, true) && null !== $this->configPath) {
                $options = array_merge($options, ['--config', $this->configPath]);
            }

            /** @var Process */
            $process = yield $this->run($this->resolveEnv($version), 'fix', ...$this->resolveExtraArgs($version, ...[
                ...$options,
                '-',
            ]));

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
            $process = yield $this->run($this->env, 'describe', ...[...$options, $rule]);

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
     * @param array<string,string> $env
     * @return Promise<Process>
     */
    public function run(array $env, string ...$args): Promise
    {
        return call(function () use ($env, $args) {
            $process = ProcessBuilder::create([PHP_BINARY, $this->binPath, ...$args])->mergeParentEnv()->env($env)->build();
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

    /**
     * @return string[]
     */
    private function resolveExtraArgs(?SemVersion $version, string ...$args): array
    {
        if ($version?->greaterThanOrEqualTo(SemVersion::fromString('3.89.2'))) {
            return [...['--allow-unsupported-php-version=yes'], ...$args];
        }

        return $args;
    }

    /**
     * @return array<string,string>
     */
    private function resolveEnv(?SemVersion $version): array
    {
        $env = $this->env;

        if (null === $version) {
            return $env;
        }

        if ($version->greaterThanOrEqualTo(SemVersion::fromString('3.89.2'))) {
            unset($env['PHP_CS_FIXER_IGNORE_ENV']);

            return $env;
        }

        $env['PHP_CS_FIXER_IGNORE_ENV'] = '1';

        return $env;
    }
}
