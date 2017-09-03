<?php

namespace Phpactor\UserInterface\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Phpactor\Application\OffsetInfo;
use Phpactor\UserInterface\Console\Dumper\DumperRegistry;

class OffsetInfoCommand extends Command
{
    /**
     * @var FileInfoAtOffset
     */
    private $infoForOffset;

    /**
     * @var DumperRegistry
     */
    private $dumperRegistry;

    public function __construct(
        OffsetInfo $infoForOffset,
        DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
        $this->infoForOffset = $infoForOffset;
        $this->dumperRegistry = $dumperRegistry;
    }

    public function configure()
    {
        $this->setName('offset:info');
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
        $this->dumperRegistry->get($format)->dump($output, $info);
    }
}
