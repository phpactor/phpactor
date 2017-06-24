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
use Phpactor\Application\InformationForOffset\InformationForOffset;

class InformationForOffsetCommand extends Command
{
    const TYPE_AUTO = 'auto';
    const TYPE_CLASS = 'class';
    const TYPE_FILE = 'file';

    private $infoForOffset;

    public function __construct(
        InformationForOffset $infoForOffset
    ) {
        parent::__construct();
        $this->infoForOffset = $infoForOffset;
    }

    public function configure()
    {
        $this->setName('offset:info');
        $this->setDescription('Return information about given file at the given offset.');
        $this->addArgument('path', InputArgument::REQUIRED, 'Source path or FQN');
        $this->addArgument('offset', InputArgument::REQUIRED, 'Destination path or FQN');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write(json_encode($this->infoForOffset->infoForOffset(
            $input->getArgument('path'),
            $input->getArgument('offset')
        )));
    }
}
