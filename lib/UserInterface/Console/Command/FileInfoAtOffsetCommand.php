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

class FileInfoAtOffsetCommand extends Command
{
    /**
     * @var FileInfoAtOffset
     */
    private $infoForOffset;

    public function __construct(
        FileInfoAtOffset $infoForOffset
    ) {
        parent::__construct();
        $this->infoForOffset = $infoForOffset;
    }

    public function configure()
    {
        $this->setName('file:offset');
        $this->setDescription('Return information about given file at the given offset');
        $this->addArgument('path', InputArgument::REQUIRED, 'Source path or FQN');
        $this->addArgument('offset', InputArgument::REQUIRED, 'Destination path or FQN');
        $this->addOption('frame', null, InputOption::VALUE_NONE, 'Show inferred frame state at offset');
        Handler\FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $info = $this->infoForOffset->infoForOffset(
            $input->getArgument('path'),
            $input->getArgument('offset'),
            $input->getOption('frame')
        );

        $format = $input->getOption('format');
        $this->dumperRegistry->get($format)->dump($output);
    }

    private function outputConsole(OutputInterface $output, array $info, int $padding = 0)
    {
    }
}
