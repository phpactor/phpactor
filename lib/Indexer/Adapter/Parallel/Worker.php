<?php

namespace Phpactor\Indexer\Adapter\Parallel;

use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

/**
 * A worker holds at most one chunk at a time and reports nothing until the
 * whole chunk is done.
 */
final class Worker
{
    private InputStream $input;

    private Process $process;

    private string $buffer = '';

    /**
     * @var list<string>
     */
    private array $chunk = [];

    /**
     * @var list<string>
     */
    private array $completed = [];

    /**
     * @var list<string>
     */
    private array $results = [];

    /**
     * @param list<string> $command
     */
    public function __construct(
        private array $command,
        private string $cwd,
    ) {
    }

    public function start(): void
    {
        $this->input = new InputStream();
        $this->process = new Process($this->command, $this->cwd, null, $this->input, null);
        $this->process->start();
    }

    public function isIdle(): bool
    {
        return [] === $this->chunk;
    }

    public function isRunning(): bool
    {
        return $this->process->isRunning();
    }

    /**
     * @param list<string> $paths
     */
    public function assign(array $paths): void
    {
        $this->chunk = $paths;
        $this->input->write(Protocol::encode(
            Protocol::FRAME_JOB,
            implode(Protocol::PATH_SEPARATOR, $paths)
        ));
    }

    /**
     * Read whatever the worker has written to us since the last call.
     */
    public function poll(): void
    {
        $this->buffer .= $this->process->getIncrementalOutput();

        // Symfony buffers the whole of a process' output in a temporary
        // stream, which here would mean accumulating the entire index.
        $this->process->clearOutput();

        foreach (Protocol::decode($this->buffer) as [$type, $payload]) {
            if (Protocol::FRAME_RECORDS !== $type) {
                continue;
            }

            $this->results[] = $payload;
            $this->completed = array_merge($this->completed, $this->chunk);
            $this->chunk = [];
        }
    }

    /**
     * Paths the worker has finished with since the last call.
     *
     * @return list<string>
     */
    public function takeCompleted(): array
    {
        $completed = $this->completed;
        $this->completed = [];

        return $completed;
    }

    /**
     * Serialized record payloads the worker has produced since the last call.
     *
     * @return list<string>
     */
    public function takeResults(): array
    {
        $results = $this->results;
        $this->results = [];

        return $results;
    }

    /**
     * The chunk a worker was working on when it died, so that the caller can
     * index it some other way. Nothing has been reported for it, so all of it
     * still needs doing.
     *
     * @return list<string>
     */
    public function takeAbandonedChunk(): array
    {
        $chunk = $this->chunk;
        $this->chunk = [];

        return $chunk;
    }

    public function errorOutput(): string
    {
        $error = $this->process->getIncrementalErrorOutput();
        $this->process->clearErrorOutput();

        return $error;
    }

    public function stop(): void
    {
        $this->input->close();
        $this->process->stop(2);
    }
}
