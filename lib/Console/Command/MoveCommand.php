<?php

namespace Phpactor\Console\Command;

use DTL\ClassMover\RefFinder\RefReplacer;
use DTL\ClassMover\Finder\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Application\ClassMover;
use Symfony\Component\Console\Input\InputArgument;

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
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $srcPath = $input->getArgument('src');
        $destPath = $input->getArgument('dest');

        $this->classMover->move($srcPath, $destPath);
    }

}
