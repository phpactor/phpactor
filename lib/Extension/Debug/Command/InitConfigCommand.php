<?php

namespace Phpactor\Extension\Debug\Command;

use Phpactor\Extension\Debug\Model\ConfigInitializer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitConfigCommand extends Command
{
    /**
     * @var ConfigInitializer
     */
    private $initializer;

    public function __construct(ConfigInitializer $initializer)
    {
        parent::__construct();
        $this->initializer = $initializer;
    }

    protected function configure(): void
    {
        $this->setDescription('Iniitalize Phpactor configuration file or update the location of the JSON schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<comment>// This command will create or update a JSON configuration file</>');
        $output->writeln('<comment>// The YAML config format is not supported by this tool</>');
        $output->writeln('');

        $action = $this->initializer->initialize();

        if ($action === ConfigInitializer::ACTION_CREATED) {
            $output->writeln(sprintf('Created %s', $this->initializer->configPath()));
            return 0;
        }

        $output->writeln(sprintf('Updated %s', $this->initializer->configPath()));

        return 0;
    }
}
