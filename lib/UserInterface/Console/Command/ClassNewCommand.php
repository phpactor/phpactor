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
use Symfony\Component\Console\Question\ChoiceQuestion;
use Phpactor\UserInterface\Console\Dumper\DumperRegistry;

class ClassNewCommand extends Command
{
    /**
     * @var DumperRegistry
     */
    private $dumperRegistry;

    /**
     * @var ClassNew
     */
    private $classNew;

    public function __construct(
        ClassNew $classNew,
        DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
        $this->dumperRegistry = $dumperRegistry;
        $this->classNew = $classNew;
    }

    public function configure()
    {
        $this->setName('class:new');
        $this->setDescription('Create new class for given path or FQN');
        $this->addArgument('src', InputArgument::REQUIRED, 'Source path or FQN');
        $this->addOption('variant', null, InputOption::VALUE_REQUIRED, 'Variant', 'default');
        $this->addOption('list', null, InputOption::VALUE_NONE, 'List variants');
        Handler\FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('list')) {
            return $this->listGenerators($input, $output);
        }

        $src = $input->getArgument('src');
        $variant = $input->getOption('variant');

        $output->writeln($this->classNew->generate($src, $variant));
    }

    private function listGenerators(InputInterface $input, OutputInterface $output)
    {
        $dumper = $this->dumperRegistry->get($input->getOption('format'));
        $dumper->dump($output, $this->classNew->availableGenerators());
    }
}
