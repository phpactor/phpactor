<?php

namespace Phpactor\UserInterface\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Application\ClassMover\ClassMover;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Phpactor;
use Phpactor\UserInterface\Console\Logger\SymfonyConsoleMoveLogger;

class MoveCommand extends Command
{
    private $mover;

    public function __construct(
        ClassMover $mover
    ) {
        parent::__construct();
        $this->mover = $mover;
    }

    public function configure()
    {
        $this->setName('mv');
        $this->setDescription('Move file (or directory) and magically update references to class contained.');
        $this->addArgument('src', InputArgument::REQUIRED, 'Source path or FQN');
        $this->addArgument('dest', InputArgument::REQUIRED, 'Destination path or FQN');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $srcPath = Phpactor::normalizePath($input->getArgument('src'));
        $destPath = Phpactor::normalizePath($input->getArgument('dest'));

        $logger = new SymfonyConsoleMoveLogger($output);
        $this->mover->move($logger, $srcPath, $destPath);
    }
}
