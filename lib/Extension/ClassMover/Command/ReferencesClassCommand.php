<?php

namespace Phpactor\Extension\ClassMover\Command;

use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Symfony\Component\Console\Command\Command;
use Phpactor\Extension\ClassMover\Application\ClassReferences;
use Phpactor\Extension\Core\Console\Dumper\DumperRegistry;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;
use Phpactor\Phpactor;
use Phpactor\Extension\Core\Console\Formatter\Highlight;
use Phpactor\Extension\Core\Console\Handler\FilesystemHandler;
use Phpactor\Extension\Core\Console\Handler\FormatHandler;

class ReferencesClassCommand extends Command
{
    public function __construct(
        private readonly ClassReferences $referenceFinder,
        private readonly DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Find and/or replace references for a given path or FQN');
        $this->addArgument('class', InputArgument::REQUIRED, 'Class path or FQN');
        $this->addOption('replace', null, InputOption::VALUE_REQUIRED, 'Replace with this Class FQN');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not write changes to files');
        FormatHandler::configure($this);
        FilesystemHandler::configure($this, SourceCodeFilesystemExtension::FILESYSTEM_GIT);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $class = $input->getArgument('class');
        $replace = $input->getOption('replace');
        $dryRun = $input->getOption('dry-run');
        $format = $input->getOption('format');
        $filesystem = $input->getOption('filesystem');

        if ($replace && $dryRun) {
            $output->writeln('<info># DRY RUN</> No files will be modified');
        }

        $results = $this->findOrReplaceReferences($filesystem, $class, $replace, $dryRun);

        if ($format) {
            $this->dumperRegistry->get($format)->dump($output, $results);
            return 0;
        }

        $output->writeln('<comment># References:</>');
        $count = $this->renderTable($output, $results, 'references', $output->isDecorated());

        if ($replace) {
            $output->write("\n");
            $output->writeln('<comment># Replacements:</>');
            $this->renderTable($output, $results, 'replacements', $output->isDecorated());
        }

        $output->write("\n");
        $output->writeln(sprintf('%s reference(s)', $count));

        return 0;
    }

    private function findOrReplaceReferences($filesystem, $class, $replace, $dryRun)
    {
        if ($replace) {
            return $this->referenceFinder->replaceReferences($filesystem, $class, $replace, $dryRun);
        }

        return $this->referenceFinder->findReferences($filesystem, $class);
    }

    private function renderTable(OutputInterface $output, array $results, string $type, bool $ansi)
    {
        $table = new Table($output);
        $table->setHeaders([
            'Path',
            'LN',
            'Line',
            'OS',
            'OE',
        ]);

        $count = 0;
        foreach ($results['references'] as $references) {
            $filePath = $references['file'];
            foreach ($references[$type] as $reference) {
                $this->addReferenceRow($table, $filePath, $reference, $ansi);
                $count++;
            }
        }

        $table->render();

        return $count;
    }

    private function addReferenceRow(Table $table, string $filePath, array $reference, bool $ansi): void
    {
        $table->addRow([
            Phpactor::relativizePath($filePath),
            $reference['line_no'],
            Highlight::highlightAtCol($reference['line'], $reference['reference'], $reference['col_no'], $ansi),
            $reference['start'],
            $reference['end'],
        ]);
    }
}
