<?php

namespace Phpactor\Extension\Completion\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Extension\Core\Console\Dumper\DumperRegistry;
use Phpactor\Extension\Completion\Application\Complete;
use Phpactor\Extension\Core\Application\Helper\FilesystemHelper;
use Phpactor\Extension\Core\Console\Handler\FormatHandler;

class CompleteCommand extends Command
{
    /**
     * @var Autocomplete
     */
    private $complete;

    /**
     * @var DumperRegistry
     */
    private $dumperRegistry;

    /**
     * @var FilesystemHelper
     */
    private $helper;

    public function __construct(
        Complete $complete,
        DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
        $this->complete = $complete;
        $this->dumperRegistry = $dumperRegistry;
        $this->helper = new FilesystemHelper();
    }

    public function configure()
    {
        $this->setName('complete');
        $this->setDescription('Suggest completions DEPRECATED! Use RPC instead');
        $this->addArgument('path', InputArgument::REQUIRED, 'STDIN, source path or FQN');
        $this->addArgument('offset', InputArgument::REQUIRED, 'Offset to complete');
        FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $completions = $this->complete->complete(
            $this->helper->contentsFromFileOrStdin($input->getArgument('path')),
            $input->getArgument('offset')
        );

        $format = $input->getOption('format');
        $this->dumperRegistry->get($format)->dump($output, $completions);
    }
}
