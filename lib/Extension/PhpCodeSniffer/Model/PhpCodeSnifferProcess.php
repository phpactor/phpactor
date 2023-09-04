<?php

namespace Phpactor\Extension\PhpCodeSniffer\Model;

use Amp\Process\Process;
use Amp\Promise;
use Phpactor\Amp\Process\ProcessBuilder;
use Phpactor\LanguageServerProtocol\TextDocumentItem;
use Phpactor\TextDocument\TextDocumentUri;
use Psr\Log\LoggerInterface;
use function Amp\ByteStream\buffer;
use function Amp\call;
use Throwable;

class PhpCodeSnifferProcess
{
    private const EXIT_FOUND_NON_FIXABLE_ERRORS = 1;
    private const EXIT_FILES_NEEDS_FIXING = 2;

    /**
     * @param array<string,string> $env
     */
    public function __construct(
        private string $binPath,
        private LoggerInterface $logger,
        private array $env = [],
    ) {
    }

    /**
     * @return Promise<Process>
     */
    public function run(string ...$args): Promise
    {
        return call(function () use ($args) {
            $process = ProcessBuilder::create([$this->binPath, ...$args])->mergeParentEnv()->env($this->env)->build();
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
     * @param  string[] $sniffs Phpcs sniffs to include.
     *
     * @return Promise<string>
     */
    public function produceFixesDiff(TextDocumentItem $textDocument, array $sniffs = []): Promise
    {
        return $this->runDiagnosticts(
            $textDocument,
            [
              '--report=diff',
              '--no-cache',
              empty($sniffs) ? '' : sprintf('--sniffs=%s', implode(',', $sniffs))
            ]
        );
    }

    /**
     * @param  string[] $options
     *
     * @return Promise<string>
     */
    public function diagnose(TextDocumentItem $textDocument, array $options = []): Promise
    {
        return $this->runDiagnosticts($textDocument, [ '--report=json', ...$options ]);
    }

    /**
     * @param  string[] $options
     *
     * @return Promise<string>
     */
    private function runDiagnosticts(TextDocumentItem $textDocument, array $options = []): Promise
    {
        return call(function () use ($textDocument, $options) {
            /** @var Process */
            $process = yield $this->run(
                ...[
                ...$options,
                '-q',
                '--no-colors',
                sprintf('--stdin-path=%s', TextDocumentUri::fromString($textDocument->uri)->path()),
                '-'
                ]
            );

            $stdin = $process->getStdin();
            $stdin->write($textDocument->text);
            $stdin->end();

            $stdout = yield buffer($process->getStdout());
            $exitCode = yield $process->join();

            if ($exitCode !== 0
                && $exitCode !== self::EXIT_FOUND_NON_FIXABLE_ERRORS
                && $exitCode !== self::EXIT_FILES_NEEDS_FIXING
            ) {
                $this->logger->error(
                    sprintf(
                        "phpcs exited with code '%s'; cmd: %s; stderr: '%s'; stdout: '%s'",
                        $exitCode,
                        $process->getCommand(),
                        yield buffer($process->getStderr()),
                        $stdout
                    )
                );
                return '[]';
            }

            return $stdout;
        });
    }
}
