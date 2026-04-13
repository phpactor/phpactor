<?php

namespace Phpactor\Indexer\Extension\Command;

use Phpactor\Indexer\Model\Indexer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class IndexOptimiseCommand extends Command
{
    private const OPT_DRY_RUN = 'dry-run';

    public function __construct(
        private Indexer $indexer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Optimise the index');
        $this->addOption(self::OPT_DRY_RUN, null, InputOption::VALUE_NONE, 'Do not make any changes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $start = microtime(true);
        $dryRun = $input->getOption(self::OPT_DRY_RUN);
        $optimisations = 0;

        $output->writeln('<info>Optimising index</info>...');

        if ($output->isVerbose()) {
            $progress = new ProgressBar(new NullOutput());
        } else {
            $progress = new ProgressBar($output);
        }
        $progress->setFormat('%current% [%bar%] %optimisations% optimisations');
        $progress->setPlaceholderFormatterDefinition('optimisations', function () use (&$optimisations): string {
            return (string)$optimisations;
        });

        foreach ($this->indexer->optimise((bool)$dryRun) as $tick) {
            if ($tick !== null) {
                $optimisations++;
                if ($output->isVerbose()) {
                    $output->writeln($tick);
                }
            }
            $progress->advance();
        }

        $progress->finish();

        $output->write("\n");
        $output->write("\n");

        if ($dryRun) {
            $output->writeln(sprintf(
                '<bg=yellow;fg=black;option>%d optimisations would have been done in %s seconds using %sb of memory</>',
                $optimisations,
                number_format(microtime(true) - $start, 2),
                number_format(memory_get_usage(true))
            ));

            return 0;
        }

        $output->writeln(sprintf(
            '<bg=green;fg=black;option>%d optimisations done in %s seconds using %sb of memory</>',
            $optimisations,
            number_format(microtime(true) - $start, 2),
            number_format(memory_get_usage(true))
        ));

        return 0;
    }
}
