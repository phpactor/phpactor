<?php

namespace Phpactor\Indexer\Extension\Command;

use Phpactor\Indexer\Adapter\Parallel\Protocol;
use Phpactor\Indexer\Adapter\Parallel\WorkerIndex;
use Phpactor\Indexer\IndexAgentBuilder;
use Phpactor\Indexer\Model\IndexJob\DocumentReader;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The child process side of parallel indexing.
 *
 * Reads chunks of file paths from stdin and writes the records they produce
 * back to stdout.
 */
class IndexWorkerCommand extends Command
{
    /**
     * @var resource
     */
    private $stdin;

    /**
     * @var resource
     */
    private $stdout;

    public function __construct(
        private IndexAgentBuilder $agentBuilder,
        private ?int $maxFileSizeToIndex,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Index files on behalf of a parent Phpactor process (internal use only)');
        $this->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->stdin = $this->open('php://stdin', 'r');
        $this->stdout = $this->open('php://stdout', 'w');

        $reader = new DocumentReader($this->maxFileSizeToIndex);

        while (null !== $job = $this->readFrame()) {
            $index = new WorkerIndex();
            $builder = $this->agentBuilder->buildIndexBuilder($index);

            foreach (explode(Protocol::PATH_SEPARATOR, $job) as $path) {
                if ('' === $path) {
                    continue;
                }

                $document = $reader->read(new SplFileInfo($path));

                if (null === $document) {
                    continue;
                }

                $builder->index($document);
            }

            $this->write(Protocol::encode(
                Protocol::FRAME_RECORDS,
                serialize($index->records())
            ));
        }

        return 0;
    }

    private function readFrame(): ?string
    {
        $header = fgets($this->stdin);

        if (false === $header) {
            return null;
        }

        $header = rtrim($header, "\n");

        if (Protocol::FRAME_JOB !== substr($header, 0, 1)) {
            return null;
        }

        return $this->read((int) substr($header, 1));
    }

    private function read(int $length): string
    {
        $data = '';

        while (($remaining = $length - strlen($data)) > 0) {
            $chunk = fread($this->stdin, $remaining);

            if (false === $chunk || '' === $chunk) {
                break;
            }

            $data .= $chunk;
        }

        return $data;
    }

    private function write(string $data): void
    {
        while ('' !== $data) {
            $written = fwrite($this->stdout, $data);

            if (false === $written) {
                throw new RuntimeException('Index worker could not write to its parent process');
            }

            $data = substr($data, $written);
        }

        fflush($this->stdout);
    }

    /**
     * @return resource
     */
    private function open(string $path, string $mode)
    {
        $stream = fopen($path, $mode);

        if (false === $stream) {
            throw new RuntimeException(sprintf('Index worker could not open "%s"', $path));
        }

        return $stream;
    }
}
