<?php

namespace Phpactor\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;

class GreetCommand extends Command
{
    public function configure()
    {
        $this->setName('greet');
        $this->setDescription('Example command');
        $this->addArgument('name', InputArgument::REQUIRED);
        $this->addOption('salutation', null, InputOption::VALUE_REQUIRED, 'hello');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf(
            '%s %s',
            $input->getOption('salutation'),
            $input->getArgument('name')
        ));
    }
}
