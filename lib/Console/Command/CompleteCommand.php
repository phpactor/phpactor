<?php

namespace Phpactor\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Reflection\ComposerReflector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Reflection\ReflectorInterface;

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
        $this->addArgument('line', InputArgument::REQUIRED);
        $this->addArgument('col', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('fqnOrFname');
        $lineNb = $input->getArgument('line');
        $columnNb = $input->getArgument('col');
        $contents = file_get_contents($name);

        $this->completer->complete($contents, $lineNb, $columnNb);
    }

    private function reflect($name)
    {
        if (!file_exists($name)) {
            return $this->reflector->reflectClass($name);
        }

        return $this->reflector->reflectFile($name);
    }
}

