<?php

namespace Phpactor\Extension\ClassMover\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Extension\ClassMover\Application\ClassCopy;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Extension\ClassMover\Command\Logger\SymfonyConsoleCopyLogger;
use Symfony\Component\Console\Input\InputOption;
use Phpactor\Extension\Core\Console\Prompt\Prompt;
use InvalidArgumentException;

class ClassCopyCommand extends Command
{
    const TYPE_AUTO = 'auto';
    const TYPE_CLASS = 'class';
    const TYPE_FILE = 'file';

    public function __construct(
        private readonly ClassCopy $copier,
        private readonly Prompt $prompt
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Copy class (path or FQN)');
        $this->addArgument('src', InputArgument::REQUIRED, 'Source path or FQN');
        $this->addArgument('dest', InputArgument::OPTIONAL, 'Destination path or FQN');
        $this->addOption('type', null, InputOption::VALUE_REQUIRED, sprintf(
            'Type of copy: "%s"',
            implode('", "', [self::TYPE_AUTO, self::TYPE_CLASS, self::TYPE_FILE])
        ), self::TYPE_AUTO);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getOption('type');
        $logger = new SymfonyConsoleCopyLogger($output);
        $src = $input->getArgument('src');
        $dest = $input->getArgument('dest');

        if (null === $dest) {
            $dest = $this->prompt->prompt('Move to: ', $src);
        }

        switch ($type) {
            case 'auto':
                $this->copier->copy($logger, $src, $dest);
                return 0;
            case 'file':
                $this->copier->copyFile($logger, $src, $dest);
                return 0;
            case 'class':
                $this->copier->copyClass($logger, $src, $dest);
                return 0;
        }

        throw new InvalidArgumentException(sprintf(
            'Invalid type "%s", must be one of: "%s"',
            $type,
            implode('", "', [ self::TYPE_AUTO, self::TYPE_FILE, self::TYPE_CLASS ])
        ));
    }
}
