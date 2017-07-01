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
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;
use Phpactor\UserInterface\Console\Prompt\Prompt;

class ClassMoveCommand extends Command
{
    const TYPE_AUTO = 'auto';
    const TYPE_CLASS = 'class';
    const TYPE_FILE = 'file';

    private $mover;
    private $prompt;

    public function __construct(
        ClassMover $mover,
        Prompt $prompt
    ) {
        parent::__construct();
        $this->mover = $mover;
        $this->prompt = $prompt;
    }

    public function configure()
    {
        $this->setName('class:move');
        $this->setDescription('Move class (by name or file path) and update all references to it');
        $this->addArgument('src', InputArgument::REQUIRED, 'Source path or FQN');
        $this->addArgument('dest', InputArgument::OPTIONAL, 'Destination path or FQN');
        $this->addOption('type', null, InputOption::VALUE_REQUIRED, sprintf(
            'Type of move: "%s"',
             implode('", "', [self::TYPE_AUTO, self::TYPE_CLASS, self::TYPE_FILE])
        ), self::TYPE_AUTO);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getOption('type');
        $logger = new SymfonyConsoleMoveLogger($output);
        $src = $input->getArgument('src');
        $dest = $input->getArgument('dest');

        if (null === $dest) {
            $dest = $this->prompt->prompt('Move to: ', $src);
        }

        switch ($type) {
            case 'auto':
                return $this->mover->move($logger, $src, $dest);
            case 'file':
                return $this->mover->moveFile($logger, $src, $dest);
            case 'class':
                return $this->mover->moveClass($logger, $src, $dest);
        }

        throw new \InvalidArgumentException(sprintf('Invalid type "%s", must be one of: "%s"',
            $type, implode('", "', [ self::TYPE_AUTO, self::TYPE_FILE, self::TYPE_CLASS ])
        ));
    }
}
