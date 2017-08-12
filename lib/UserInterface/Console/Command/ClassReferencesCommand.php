<?php

namespace Phpactor\UserInterface\Console\Command;

use Phpactor\UserInterface\Console\Command\ClassReferencesCommand;
use Symfony\Component\Console\Command\Command;
use Phpactor\Application\ClassReferences;
use Phpactor\UserInterface\Console\Dumper\DumperRegistry;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\Table;
use Phpactor\Phpactor;
use Symfony\Component\Console\Input\InputOption;

class ClassReferencesCommand extends Command
{
    /**
     * @var ClassReferences
     */
    private $referenceFinder;

    /**
     * @var DumperRegistry
     */
    private $dumperRegistry;

    public function __construct(
        ClassReferences $referenceFinder,
        DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
        $this->referenceFinder = $referenceFinder;
        $this->dumperRegistry = $dumperRegistry;
    }

    public function configure()
    {
        $this->setName('class:references');
        $this->setDescription('Move class (by name or file path) and update all references to it');
        $this->addArgument('class', InputArgument::REQUIRED, 'Class path or FQN');
        $this->addOption('replace', null, InputOption::VALUE_REQUIRED, 'Replace with this Class FQN');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not write changes to files');
        Handler\FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $class = $input->getArgument('class');
        $replace = $input->getOption('replace');
        $dryRun = $input->getOption('dry-run');


        if ($replace && $dryRun) {
            $output->writeln('<info># DRY RUN</> No files will be modified');
        }

        if ($replace) {
            $results = $this->referenceFinder->replaceReferences($class, $replace, $dryRun);
        } else {
            $results = $this->referenceFinder->findReferences($class);
        }


        $format = $input->getOption('format');

        if ($format) {
            $this->dumperRegistry->get($format)->dump($output, $results);
            return;
        }

        $output->writeln('<comment># References:</>');
        $count = $this->renderTable($output, $results, 'references');

        if ($replace) {
            $output->write(PHP_EOL);
            $output->writeln('<comment># Replacements:</>');
            $this->renderTable($output, $results, 'replacements');
        }

        $output->write(PHP_EOL);
        $output->write(sprintf('%s reference(s)', $count));
    }

    private function renderTable(OutputInterface $output, array $results, $type)
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
                $this->addReferenceRow($table, $filePath, $reference);
                $count++;
            }
        }

        $table->render();

        return $count;
    }

    private function addReferenceRow(Table $table, string $filePath, array $reference)
    {
            $table->addRow([
                Phpactor::relativizePath($filePath),
                $reference['line_no'],
                $this->formatLine($reference['line'], $reference['reference'], $reference['start'], $reference['end']),
                $reference['start'],
                $reference['end'],
            ]);
    }

    private function formatLine(string $line, string $reference)
    {
        $formatted = str_replace($reference, '<bright>' . $reference . '</>', $line);

        return $formatted;
    }
}

