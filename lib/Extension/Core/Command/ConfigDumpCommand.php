<?php

namespace Phpactor\Extension\Core\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Extension\Core\Console\Dumper\DumperRegistry;
use Phpactor\Config\ConfigLoader;
use Symfony\Component\Console\Input\InputOption;
use Phpactor\Config\Paths;
use Phpactor\Extension\Core\Console\Handler\FormatHandler;

class ConfigDumpCommand extends Command
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var DumperRegistry
     */
    private $registry;

    /**
     * @var Paths
     */
    private $paths;

    public function __construct(
        array $config,
        DumperRegistry $registry,
        Paths $paths
    ) {
        parent::__construct();

        $this->config = $config;
        $this->registry = $registry;
        $this->paths = $paths;
    }

    public function configure()
    {
        $this->setName('config:dump');
        $this->setDescription('Show loaded config files and dump current configuration.');
        $this->addOption('config-only', null, InputOption::VALUE_NONE, 'Do not output configuration file locations');
        FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $format = $input->getOption('format');

        if (false === $input->getOption('config-only')) {
            $output->writeln('<info>Config files:</>');
            foreach ($this->paths->configFiles() as $i => $file) {
                if (!file_exists($file)) {
                    $output->write(' [<error>𐄂</>]');
                } else {
                    $output->write(' [<info>✔</>]');
                }
                $output->writeln(' ' .$file);
            }
            $output->write(PHP_EOL);
        }

        $this->registry->get($format)->dump($output, $this->config);
    }
}
