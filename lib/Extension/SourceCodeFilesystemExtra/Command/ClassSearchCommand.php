<?php

namespace Phpactor\Extension\SourceCodeFilesystemExtra\Command;

use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\Extension\SourceCodeFilesystemExtra\SourceCodeFilestem\Application\ClassSearch;
use Phpactor\Extension\Core\Console\Dumper\DumperRegistry;
use Phpactor\Extension\Core\Console\Handler\FormatHandler;
use Phpactor\Extension\Core\Console\Handler\FilesystemHandler;

class ClassSearchCommand extends Command
{
    public function __construct(
        private readonly ClassSearch $search,
        private readonly DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Search for class by (short) name and return informations on candidates');
        $this->addArgument('name', InputArgument::REQUIRED, 'Source path or FQN');
        FormatHandler::configure($this);
        FilesystemHandler::configure($this, SourceCodeFilesystemExtension::FILESYSTEM_COMPOSER);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $results = $this->search->classSearch(
            $input->getOption('filesystem'),
            $input->getArgument('name')
        );

        $dumper = $this->dumperRegistry->get($input->getOption('format'));
        $dumper->dump($output, $results);

        return 0;
    }
}
