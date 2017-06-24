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
use Phpactor\Application\ClassSearch\ClassSearch;

class ClassSearchCommand extends Command
{
    public function __construct(
        ClassSearch $search
    ) {
        parent::__construct();
        $this->search = $search;
    }

    public function configure()
    {
        $this->setName('class:search');
        $this->setDescription('Search for class by (short) name and return informations on candidates');
        $this->addArgument('name', InputArgument::REQUIRED, 'Source path or FQN');
        Handler\FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $results = $this->search->classSearch($input->getArgument('name'));
        $format = $input->getOption('format');

        switch ($format) {
            case Handler\FormatHandler::FORMAT_JSON:
                $output->write(json_encode($results));
                return;
            case Handler\FormatHandler::FORMAT_CONSOLE:
                return $this->outputConsole($output, $results);
        }

        throw new \InvalidArgumentException(sprintf(
            'Invalid format "%s", known formats: "%s"',
            $format, implode('", "', Handler\FormatHandler::VALID_FORMATS)
        ));
    }

    private function outputConsole(OutputInterface $output, array $results)
    {
        foreach ($results as $result) {
            if (!$result['name']) {
                continue;
            }

            $output->writeln(sprintf('%s:%s', $result['name'], $result['path']));
        }
    }
}
