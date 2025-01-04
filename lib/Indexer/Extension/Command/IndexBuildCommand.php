<?php

namespace Phpactor\Indexer\Extension\Command;

use Amp\Loop;
use Phpactor\AmpFsWatch\Watcher;
use Phpactor\Indexer\Model\Indexer;
use Phpactor\Indexer\Model\MemoryUsage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Cast\Cast;
use Symfony\Component\Filesystem\Path;

class IndexBuildCommand extends Command
{
    const ARG_SUB_PATH = 'sub-path';
    const OPT_RESET = 'reset';
    const OPT_WATCH = 'watch';

    private MemoryUsage $usage;

    public function __construct(private Indexer $indexer, private Watcher $watcher)
    {
        parent::__construct();
        $this->usage = MemoryUsage::create();
    }

    protected function configure(): void
    {
        $this->setDescription('Build the index');
        $this->addArgument(self::ARG_SUB_PATH, InputArgument::OPTIONAL, 'Sub path to index');
        $this->addOption(self::OPT_RESET, null, InputOption::VALUE_NONE, 'Purge index before building');
        $this->addOption(self::OPT_WATCH, null, InputOption::VALUE_NONE, 'Watch for updated files (poll for changes ever x seconds, default 10)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $subPath = Cast::toStringOrNull($input->getArgument(self::ARG_SUB_PATH));
        $watch = Cast::toBool($input->getOption(self::OPT_WATCH));

        if ($input->getOption(self::OPT_RESET)) {
            $this->indexer->reset();
        }

        if (is_string($subPath)) {
            $subPath = Path::join(
                Cast::toStringOrNull(getcwd()),
                $subPath
            );
        }

        $this->buildIndex($output, $subPath);

        if ($watch) {
            $this->watch($output);
        }

        return 0;
    }

    private function buildIndex(OutputInterface $output, ?string $subPath = null): void
    {
        $start = microtime(true);

        $output->write('<info>Building job</info>...');
        $job = $this->indexer->getJob($subPath);
        $output->writeln('done');
        $output->writeln('<info>Building index:</info>');
        $output->write("\n");

        if ($job->size() === 0) {
            $output->writeln('No files found');
            return;
        }

        $progress = new ProgressBar($output, $job->size(), 0.001);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progress->setPlaceholderFormatterDefinition('memory', function () {
            return MemoryUsage::create()->memoryUsageFormatted();
        });
        foreach ($job->generator() as $filePath) {
            if ($output->isVerbose()) {
                $output->writeln(sprintf('Updated %s', $filePath));
                continue;
            }
            $progress->advance();
        }

        $progress->finish();
        $output->write("\n");
        $output->write("\n");

        $output->writeln(sprintf(
            '<bg=green;fg=black;option>Done in %s seconds using %sb of memory</>',
            number_format(microtime(true) - $start, 2),
            number_format(memory_get_usage(true))
        ));
    }

    private function watch(OutputInterface $output): void
    {
        Loop::run(function () use ($output) {
            $process = yield $this->watcher->watch();

            // Signals are not supported on Windows
            if (defined('SIGINT')) {
                Loop::onSignal(SIGINT, function () use ($output, $process): void {
                    $output->write('Shutting down watchers...');
                    $process->stop();
                    $output->writeln('done');
                    Loop::stop();
                });
            }

            $output->writeln(sprintf('<info>Watching for file changes with </>%s<info>...</>', $this->watcher->describe()));

            while (null !== $file = yield $process->wait()) {
                $job = $this->indexer->getJob($file->path());
                foreach ($job->generator() as $filePath) {
                    $output->writeln(sprintf('Updating %s', $filePath));
                }
            }
        });
    }
}
