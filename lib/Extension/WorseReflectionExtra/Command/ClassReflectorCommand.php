<?php

namespace Phpactor\Extension\WorseReflectionExtra\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Extension\WorseReflectionExtra\Application\ClassReflector;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Extension\Core\Console\Dumper\DumperRegistry;
use Phpactor\Extension\Core\Console\Handler\FormatHandler;

class ClassReflectorCommand extends Command
{
    public function __construct(
        private ClassReflector $reflector,
        private DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Reflect a given class (path or FQN)');
        $this->addArgument('name', InputArgument::REQUIRED, 'Source path or FQN');
        FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string $name */
        $name = $input->getArgument('name');

        $reflection = $this->reflector->reflect($name);
        $this->dumperRegistry->get($input->getOption('format'))->dump($output, $reflection);

        return 0;
    }
}
