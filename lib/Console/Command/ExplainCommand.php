<?php

namespace Phpactor\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Reflection\ComposerReflector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Reflection\ReflectorInterface;

class ExplainCommand extends Command
{
    public function __construct(
        ReflectorInterface $reflector
    )
    {
        parent::__construct();
        $this->reflector = $reflector;
    }

    public function configure()
    {
        $this->setName('explain');
        $this->setDescription('Explain a class by its class FQN or filename');
        $this->addArgument('fqnOrFname', InputArgument::REQUIRED, 'Fully qualified class name or filename');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('fqnOrFname');

        $reflection = $this->reflect($name);
        $output->writeln((string) $reflection);
    }

    private function reflect($name)
    {
        if (!file_exists($name)) {
            return $this->reflector->reflectClass($name);
        }

        return $this->reflector->reflectFile($name);
    }
}
