<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\Model;

use function Amp\ByteStream\buffer;
use function Amp\call;

use Amp\Process\Process;
use Amp\Promise;
use Phpactor\Amp\Process\ProcessBuilder;
use Phpactor\Extension\LanguageServerPhpCsFixer\Exception\PhpCsFixerError;
use Psr\Log\LoggerInterface;
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
        private array $env = [],
        private ?string $configPath = null,
        private ?string $wrapper = null
    ) {
    }

    /**
     * @param string[] $options
     *
     * @return Promise<string>
     */
    public function fix(string $content, array $options = []): Promise
    {
        return call(function () use ($content, $options) {
            if (false === array_search('--rules', $options, true) && null !== $this->configPath) {
                $options = array_merge($options, ['--config', $this->configPath]);
            }

            /** @var Process */
            $process = yield $this->run('fix', ...[...$options, '-']);

            $stdin = $process->getStdin();
            $stdin->write($content);
            $stdin->end();

            $stdout = yield buffer($process->getStdout());
            $exitCode = yield $process->join();

            if (0 !== $exitCode
                && self::EXIT_SOME_FILES_INVALID !== $exitCode
                && self::EXIT_FILES_NEEDS_FIXING !== $exitCode
                && $exitCode !== (self::EXIT_SOME_FILES_INVALID | self::EXIT_FILES_NEEDS_FIXING)
            ) {
                throw new PhpCsFixerError(
                    $exitCode,
                    $process->getCommand(),
                    yield buffer($process->getStderr()),
                    $stdout
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

            if (0 !== $exitCode) {
                throw new PhpCsFixerError(
                    $exitCode,
                    $process->getCommand(),
                    yield buffer($process->getStderr()),
                    $stdout
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
            if (null !== $this->wrapper) {
                $envVars = '';
                foreach ($this->env as $key => $value) {
                    $envVars .= sprintf('%s=%s ', $key, $value);
                }

                $phpCsFixerCommand = sprintf(
                    '%s%s %s',
                    $envVars,
                    $this->binPath,
                    implode(' ', $args)
                );

                $process = ProcessBuilder::create([...explode(' ', $this->wrapper), $phpCsFixerCommand])->mergeParentEnv()->build();
            } else {
                $process = ProcessBuilder::create([PHP_BINARY, $this->binPath, ...$args])->mergeParentEnv()->env($this->env)->build();
            }

            yield $process->start();

            $process->join()
                ->onResolve(function (?Throwable $error, $data) use ($process): void {
                    $this->logger->log(
                        $error ? 'warning' : 'debug',
                        sprintf(
                            'Executed %s, which exited with %s',
                            $process->getCommand(),
                            $data
                        )
                    );
                })
            ;

            return $process;
        });
    }
}
