<?php

namespace Phpactor\UserInterface\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Application\ClassMover\ClassMover;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Phpactor;
use Phpactor\UserInterface\Console\Logger\SymfonyConsoleMoveLogger;
use Symfony\Component\Console\Input\InputOption;

class MoveCommand extends Command
{
    const TYPE_AUTO = 'auto';
    const TYPE_CLASS = 'class';
    const TYPE_FILE = 'file';

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
        $this->addOption('type', null, InputOption::VALUE_REQUIRED, sprintf(
            'Type of move: "%s"',
             implode('", "', [self::TYPE_AUTO, self::TYPE_CLASS, self::TYPE_FILE])
        ), self::TYPE_AUTO);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getOption('type');
        $logger = new SymfonyConsoleMoveLogger($output);

        switch ($type) {
            case 'auto':
                return $this->mover->move($logger, $input->getArgument('src'), $input->getArgument('dest'));
            case 'file':
                return $this->mover->moveFile($logger, $input->getArgument('src'), $input->getArgument('dest'));
            case 'class':
                return $this->mover->moveClass($logger, $input->getArgument('src'), $input->getArgument('dest'));
        }

        throw new \InvalidArgumentException(sprintf('Invalid type "%s", must be one of: "%s"',
            $type, implode('", "', [ self::TYPE_AUTO, self::TYPE_FILE, self::TYPE_CLASS ])
        ));
    }
}
