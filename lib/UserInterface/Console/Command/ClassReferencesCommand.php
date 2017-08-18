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
use Symfony\Component\Console\Input\InputOption;
use Phpactor\Phpactor;

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
        $this->setName('references:class');
        $this->setDescription('Find and/or replace references for a given path or FQN');
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
        $format = $input->getOption('format');

        if ($replace && $dryRun) {
            $output->writeln('<info># DRY RUN</> No files will be modified');
        }

        $results = $this->findOrReplaceReferences($class, $replace, $dryRun);

        if ($format) {
            $this->dumperRegistry->get($format)->dump($output, $results);
            return;
        }

        $output->writeln('<comment># References:</>');
        $count = $this->renderTable($output, $results, 'references', $output->isDecorated());

        if ($replace) {
            $output->write(PHP_EOL);
            $output->writeln('<comment># Replacements:</>');
            $this->renderTable($output, $results, 'replacements', $output->isDecorated());
        }

        $output->write(PHP_EOL);
        $output->writeln(sprintf('%s reference(s)', $count));
    }

    private function findOrReplaceReferences($class, $replace, $dryRun)
    {
        if ($replace) {
           return $this->referenceFinder->replaceReferences($class, $replace, $dryRun);
        }

        return $this->referenceFinder->findReferences($class);
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

    private function addReferenceRow(Table $table, string $filePath, array $reference, bool $ansi)
    {
        $table->addRow([
            Phpactor::relativizePath($filePath),
            $reference['line_no'],
            $this->formatLine($reference['line'], $reference['reference'], $reference['col_no'], $ansi),
            $reference['start'],
            $reference['end'],
        ]);
    }

    private function formatLine(string $line, string $reference, int $col, bool $ansi)
    {
        $leftBracket = '⟶';
        $rightBracket = '⟵';

        if ($ansi) {
            $leftBracket = '<highlight>';
            $rightBracket = '</>';
        }

        return sprintf(
            '%s%s%s%s%s',
            substr($line, 0, $col),
            $leftBracket,
            $reference,
            $rightBracket,
            substr($line, $col + strlen($reference))
        );
    }
}

