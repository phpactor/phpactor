<?php

namespace Phpactor\UserInterface\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Application\ClassInformationForOffsetr\ClassInformationForOffsetr;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Phpactor;
use Phpactor\UserInterface\Console\Logger\SymfonyConsoleInformationForOffsetLogger;
use Symfony\Component\Console\Input\InputOption;
use Phpactor\Application\FileInfoAtOffset;
use Phpactor\UserInterface\Console\Dumper\DumperRegistry;
use Phpactor\Application\Autocomplete;

class AutocompleteCommand extends Command
{
    /**
     * @var Autocomplete
     */
    private $autocomplete;

    public function __construct(
        Autocomplete $autocomplete,
        DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
        $this->autocomplete = $autocomplete;
        $this->dumperRegistry = $dumperRegistry;
    }

    public function configure()
    {
        $this->setName('complete');
        $this->setDescription('Suggest completions for the given offset');
        $this->addArgument('path', InputArgument::REQUIRED, 'STDIN, source path or FQN');
        $this->addArgument('offset', InputArgument::REQUIRED, 'Offset to complete');
        Handler\FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $completions = $this->autocomplete->autocomplete(
            $input->getArgument('path'),
            $input->getArgument('offset')
        );

        $format = $input->getOption('format');
        $this->dumperRegistry->get($format)->dump($output, $completions);
    }
}
