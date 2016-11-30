<?php

namespace Phpactor\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Reflection\ComposerReflector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Reflection\ReflectorInterface;
use Phpactor\Complete\Completer;

class CompleteCommand extends Command
{
    public function __construct(
        Completer $completer
    )
    {
        parent::__construct();
        $this->completer = $completer;
    }

    public function configure()
    {
        $this->setName('complete');
        $this->setDescription('Explain a class by its class FQN or filename');
        $this->addArgument('fqnOrFname', InputArgument::REQUIRED, 'Fully qualified class name or filename');
        $this->addArgument('offset', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('fqnOrFname');
        $offset = $input->getArgument('offset');
        $contents = file_get_contents($name);

        $completions = $this->completer->complete($contents, $offset);

        $output->writeln(json_encode($completions->all(), JSON_PRETTY_PRINT));
    }
}
