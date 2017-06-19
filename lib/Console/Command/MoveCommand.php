<?php

namespace Phpactor\Console\Command;

use DTL\ClassMover\RefFinder\RefReplacer;
use DTL\ClassMover\Finder\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Application\ClassMover;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Phpactor;
use Symfony\Component\Console\Input\InputOption;
use Phpactor\Application\ClassMover\MoveLogger;
use Phpactor\Console\Logger\SymfonyConsoleMoveLogger;

class MoveCommand extends Command
{
    private $mover;

    public function __construct(
        ClassMover $mover
    )
    {
        parent::__construct();
        $this->mover = $mover;
    }

    public function configure()
    {
        $this->setName('mv');
        $this->setDescription('Move file (or directory) and magically update references to class contained.');
        $this->addArgument('src', InputArgument::REQUIRED, 'Source path');
        $this->addArgument('dest', InputArgument::REQUIRED, 'Destination path');
        $this->addOption('path', null, InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'File path(s) in which to replace references');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $srcPath = Phpactor::normalizePath($input->getArgument('src'));
        $destPath = Phpactor::normalizePath($input->getArgument('dest'));
        $refSearchPaths = array_map(function($path) {
            return Phpactor::normalizePath($path);
        }, $input->getOption('path'));

        $logger = new SymfonyConsoleMoveLogger($output);
        $this->mover->move($logger, $srcPath, $destPath, $refSearchPaths);
    }

}
