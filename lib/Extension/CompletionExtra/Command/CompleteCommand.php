<?php

namespace Phpactor\Extension\CompletionExtra\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Extension\Core\Console\Dumper\DumperRegistry;
use Phpactor\Extension\CompletionExtra\Application\Complete;
use Phpactor\Extension\Core\Application\Helper\FilesystemHelper;
use Phpactor\Extension\Core\Console\Handler\FormatHandler;

class CompleteCommand extends Command
{
    private readonly FilesystemHelper $helper;

    public function __construct(
        private readonly Complete $complete,
        private readonly DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
        $this->helper = new FilesystemHelper();
    }

    public function configure(): void
    {
        $this->setDescription('Suggest completions DEPRECATED! Use RPC instead');
        $this->addArgument('path', InputArgument::REQUIRED, 'STDIN, source path or FQN');
        $this->addArgument('offset', InputArgument::REQUIRED, 'Offset to complete');
        $this->addOption('type', null, InputOption::VALUE_REQUIRED, 'Type of completion (e.g. php)', 'php');
        FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $completions = $this->complete->complete(
            $this->helper->contentsFromFileOrStdin($input->getArgument('path')),
            $input->getArgument('offset'),
            $input->getOption('type')
        );

        $format = $input->getOption('format');
        $this->dumperRegistry->get($format)->dump($output, $completions);

        return 0;
    }
}
