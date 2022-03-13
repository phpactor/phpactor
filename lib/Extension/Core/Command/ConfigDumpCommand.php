<?php

namespace Phpactor\Extension\Core\Command;

use Phpactor\ConfigLoader\Core\PathCandidates;
use Phpactor\FilePathResolver\Expanders;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Extension\Core\Console\Dumper\DumperRegistry;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Terminal;

class ConfigDumpCommand extends Command
{
    private array $config;

    private DumperRegistry $registry;

    private PathCandidates $paths;

    private Expanders $expanders;

    public function __construct(
        array $config,
        DumperRegistry $registry,
        PathCandidates $paths,
        Expanders $expanders
    ) {
        parent::__construct();

        $this->config = $config;
        $this->registry = $registry;
        $this->paths = $paths;
        $this->expanders = $expanders;
    }

    public function configure(): void
    {
        $this->setDescription('Show loaded config files and dump current configuration.');
        $this->addOption('config-only', null, InputOption::VALUE_NONE, 'Do not output configuration file locations');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (false === $input->getOption('config-only')) {
            $this->dumpMetaInformation($output);
        }

        $output->writeln(json_encode($this->config, JSON_PRETTY_PRINT));

        return 0;
    }

    private function dumpMetaInformation(OutputInterface $output): void
    {
        $output->writeln('<info>Config files:</>');
        $output->write(PHP_EOL);
        foreach ($this->paths as $candidate) {
            if (!file_exists($candidate->path())) {
                $output->write('  [✖]');
            } else {
                $output->write('  [<info>✔</>]');
            }
            $output->writeln(' ' .$candidate->path());
        }
        
        $output->write(PHP_EOL);
        $output->writeln('<info>File path tokens:</info>');
        $output->write(PHP_EOL);
        foreach ($this->expanders->toArray() as $tokenName => $value) {
            $output->writeln(sprintf('  <comment>%%%s%%</>: %s', $tokenName, $value));
        }
        $terminal = new Terminal();
        $output->write(PHP_EOL);
        $output->writeln(str_repeat('-', $terminal->getWidth()));
        $output->write(PHP_EOL);
    }
}
