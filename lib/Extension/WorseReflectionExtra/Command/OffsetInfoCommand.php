<?php

namespace Phpactor\Extension\WorseReflectionExtra\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Phpactor\Extension\WorseReflectionExtra\Application\OffsetInfo;
use Phpactor\Extension\Core\Console\Dumper\DumperRegistry;
use Phpactor\Extension\Core\Console\Handler\FormatHandler;

class OffsetInfoCommand extends Command
{
    public function __construct(
        private readonly OffsetInfo $infoForOffset,
        private readonly DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Return information about given file at the given offset');
        $this->addArgument('path', InputArgument::REQUIRED, 'Source path or FQN');
        $this->addArgument('offset', InputArgument::REQUIRED, 'Destination path or FQN');
        $this->addOption('frame', null, InputOption::VALUE_NONE, 'Show inferred frame state at offset');
        FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $info = $this->infoForOffset->infoForOffset(
            $input->getArgument('path'),
            $input->getArgument('offset'),
            $input->getOption('frame')
        );

        $format = $input->getOption('format');
        $this->dumperRegistry->get($format)->dump($output, $info);

        return 0;
    }
}
