<?php

namespace Phpactor\UserInterface\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Application\ClassSearch;

class ClassSearchCommand extends Command
{
    public function __construct(
        ClassSearch $search
    ) {
        parent::__construct();
        $this->search = $search;
    }

    public function configure()
    {
        $this->setName('class:search');
        $this->setDescription('Search for class by (short) name and return informations on candidates');
        $this->addArgument('name', InputArgument::REQUIRED, 'Source path or FQN');
        Handler\FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $results = $this->search->classSearch($input->getArgument('name'));
        $format = $input->getOption('format');

        $output->write(json_encode($results));
    }

    private function outputConsole(OutputInterface $output, array $results)
    {
        foreach ($results as $result) {
            if (!$result['class']) {
                continue;
            }

            $output->writeln(sprintf('<comment>%s</><info>:</>%s', $result['class'], $result['file_path']));
        }
    }
}
