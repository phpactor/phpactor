<?php

namespace Phpactor\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Phpactor\Application\OffsetInfo;
use Phpactor\Console\Dumper\DumperRegistry;
use Phpactor\Application\OffsetDefinition;

class OffsetGotoDefinitionCommand extends Command
{
    /**
     * @var FileInfoAtOffset
     */
    private $gotoDefinition;

    /**
     * @var DumperRegistry
     */
    private $dumperRegistry;

    public function __construct(
        OffsetDefinition $gotoDefinition,
        DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
        $this->gotoDefinition = $gotoDefinition;
        $this->dumperRegistry = $dumperRegistry;
    }

    public function configure()
    {
        $this->setName('offset:definition');
        $this->setDescription('Return path and offset of the definition of the symbol at given offset');
        $this->addArgument('path', InputArgument::REQUIRED, 'Source path or FQN');
        $this->addArgument('offset', InputArgument::REQUIRED, 'Destination path or FQN');
        Handler\FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $info = $this->gotoDefinition->gotoDefinition(
            $input->getArgument('path'),
            $input->getArgument('offset')
        );

        $format = $input->getOption('format');
        $this->dumperRegistry->get($format)->dump($output, $info);
    }
}
