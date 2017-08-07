<?php

namespace Phpactor\UserInterface\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\DependencyInjection\Container;
use Phpactor\UserInterface\Console\Dumper\DumperRegistry;
use Phpactor\Config\ConfigLoader;
use Symfony\Component\Console\Input\InputOption;

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
     * @var ConfigLoader
     */
    private $configLoader;

    public function __construct(
        array $config,
        DumperRegistry $registry,
        ConfigLoader $configLoader
    ) {
        parent::__construct();

        $this->config = $config;
        $this->registry = $registry;
        $this->configLoader = $configLoader;
    }

    public function configure()
    {
        $this->setName('config:dump');
        $this->addOption('config-only', null, InputOption::VALUE_NONE, 'Do not output configuration file locations');
        Handler\FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $format = $input->getOption('format');

        if (false === $input->getOption('config-only')) {
            $output->writeln('<info>Config files:</>');
            foreach ($this->configLoader->configFiles() as $i => $file) {
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


