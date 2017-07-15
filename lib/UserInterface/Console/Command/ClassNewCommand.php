<?php

namespace Phpactor\UserInterface\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Application\ClassCopy;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Phpactor;
use Phpactor\UserInterface\Console\Logger\SymfonyConsoleCopyLogger;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\Question;
use Phpactor\UserInterface\Console\Prompt\Prompt;
use Phpactor\Application\ClassNew;

class ClassNewCommand extends Command
{
    /**
     * @var ClassNew
     */
    private $classNew;

    public function __construct(
        ClassNew $classNew
    ) {
        parent::__construct();
        $this->classNew = $classNew;
    }

    public function configure()
    {
        $this->setName('class:new');
        $this->setDescription('Create new class for given path or FQN');
        $this->addArgument('src', InputArgument::REQUIRED, 'Source path or FQN');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $src = $input->getArgument('src');

        $output->writeln($this->classNew->generate($src));
    }
}
