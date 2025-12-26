<?php

namespace Phpactor\Extension\PhpCodeSniffer\Model;

use Amp\Process\Process;
use Amp\Promise;
use Phpactor\Amp\Process\ProcessBuilder;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\TextDocument\TextDocumentUri;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;
use function Amp\ByteStream\buffer;
use function Amp\call;

class PhpCodeSnifferProcess
{
    private const EXIT_FOUND_NON_FIXABLE_ERRORS = 1;
    private const EXIT_FILES_NEEDS_FIXING = 2;

    /**
     * @param array<string,string> $env
     * @param list<string> $additionalArgs
     */
    public function __construct(
        private readonly string $binPath,
        private readonly LoggerInterface $logger,
        private readonly array $env = [],
        private readonly array $additionalArgs = [],
        private readonly ?string $cwd = null
    ) {
    }

    /**
     * @return Promise<Process>
     */
    public function run(string ...$args): Promise
    {
        $args = array_merge($args, $this->additionalArgs);
        return call(function () use ($args) {
            $process = ProcessBuilder::create([
                PHP_BINARY,
                '-d',
                'display_errors=stderr',
                '-d',
                'error_reporting=24575',
                $this->binPath,
                ...$args
            ])->mergeParentEnv()->env($this->env);
            if ($this->cwd !== null) {
                $process->cwd($this->cwd);
            }
            $process = $process->build();
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
                });

            return $process;
        });
    }

    /**
     * Producing diffs for phpcs fixes requires a temporary
     * file. Otherwise any changes in current buffer which are not saved
     * are included in resulted diff and interpreted as diagnostics with
     * misleading ranges.
     *
     * It is because phpcs simply calls system's `diff` with the file
     * passed by `--stdin-path` option.
     *
     * @param  string[] $sniffs Phpcs sniffs to include.
     *
     * @return Promise<string>
     */
    public function produceFixesDiff(TextDocumentItem $textDocument, array $sniffs = []): Promise
    {
        return call(function () use ($textDocument, $sniffs) {
            $tmpFilePath = $this->createTempFile($textDocument->text);
            if (null === $tmpFilePath) {
                $this->logger->error(
                    'Failed to create temporary file for phpcs diagnostics. Without this results would be unreliable.'
                );
                return '[]';
            }
            $diagnostics = yield $this->runDiagnosticts(
                $tmpFilePath,
                $textDocument->text,
                [
                  '--report=diff',
                  '--no-cache',
                  empty($sniffs) ? '' : sprintf('--sniffs=%s', implode(',', $sniffs))
                ]
            );
            unlink($tmpFilePath);
            return $diagnostics;
        });
    }

    /**
     * @param  string[] $options
     *
     * @return Promise<string>
     */
    public function diagnose(TextDocumentItem $textDocument, array $options = []): Promise
    {
        return $this->runDiagnosticts(
            TextDocumentUri::fromString($textDocument->uri)->path(),
            $textDocument->text,
            [ '--report=json', ...$options ]
        );
    }

    /**
     * @param  string[] $options
     *
     * @return Promise<string>
     */
    private function runDiagnosticts(string $url, string $text, array $options = []): Promise
    {
        return call(function () use ($url, $text, $options) {
            /** @var Process */
            $process = yield $this->run(
                ...[
                ...$options,
                '-q',
                '--no-colors',
                sprintf('--stdin-path=%s', $url),
                '-'
                ]
            );

            $stdin = $process->getStdin();
            $stdin->write($text);
            $stdin->end();

            $stdout = yield buffer($process->getStdout());
            $exitCode = yield $process->join();

            if ($exitCode !== 0
                && $exitCode !== self::EXIT_FOUND_NON_FIXABLE_ERRORS
                && $exitCode !== self::EXIT_FILES_NEEDS_FIXING
            ) {
                throw new RuntimeException(
                    sprintf(
                        "phpcs exited with code '%s'; cmd: %s; stderr: '%s'; stdout: '%s'",
                        $exitCode,
                        $process->getCommand(),
                        yield buffer($process->getStderr()),
                        $stdout
                    )
                );
            }

            return $stdout;
        });
    }

    /**
     * Filename MUST include PHP extension, otherwise phpcs will not
     * process it.
     */
    private function createTempFile(string $text): ?string
    {
        $tmpName = tempnam(sys_get_temp_dir(), 'phpcsls');
        $name = sprintf('%s.php', $tmpName);
        if (false === rename($tmpName, $name)) {
            throw new RuntimeException('Could not rename file');
        }
        $written = file_put_contents($name, $text);

        if (false === $written) {
            return null;
        }

        return $name;
    }
}
