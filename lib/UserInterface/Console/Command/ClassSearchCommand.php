<?php

namespace Phpactor\UserInterface\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Application\ClassSearchr\ClassSearchr;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Phpactor;
use Phpactor\UserInterface\Console\Logger\SymfonyConsoleSearchLogger;
use Symfony\Component\Console\Input\InputOption;

class ClassSearchCommand extends Command
{
    const TYPE_AUTO = 'auto';
    const TYPE_CLASS = 'class';
    const TYPE_FILE = 'file';

    private $mover;

    public function __construct(
        ClassSearchr $mover
    ) {
        parent::__construct();
        $this->mover = $mover;
    }

    public function configure()
    {
        $this->setName('class:search');
        $this->setDescription('Search for class by (short) name and return informations on candidates');
        $this->addArgument('name', InputArgument::REQUIRED, 'Source path or FQN');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
