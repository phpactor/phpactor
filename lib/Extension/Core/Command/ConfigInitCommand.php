<?php

namespace Phpactor\Extension\Core\Command;

use Phpactor\Configurator\Model\ConfigManipulator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigInitCommand extends Command
{
    public function __construct(private ConfigManipulator $initializer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Iniitalize Phpactor configuration file or update the location of the JSON schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<comment>// This command will create or update a JSON configuration file</>');
        $output->writeln('<comment>// The YAML config format is not supported by this tool</>');

        $created = !file_exists($this->initializer->configPath());
        $action = $this->initializer->initialize();

        if ($created) {
            $output->writeln(sprintf('Created %s', $this->initializer->configPath()));
            return 0;
        }

        $output->writeln(sprintf('<info>Updated:</> %s', $this->initializer->configPath()));

        return 0;
    }
}
