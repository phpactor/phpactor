<?php

namespace Phpactor\UserInterface\Console\Command;

use Phpactor\UserInterface\Console\Command\ReferencesMethodCommand;
use Symfony\Component\Console\Command\Command;
use Phpactor\Application\ClassReferences;
use Phpactor\UserInterface\Console\Dumper\DumperRegistry;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;
use Phpactor\Phpactor;
use Phpactor\UserInterface\Console\Formatter\Highlight;
use Phpactor\Application\ClassMethodReferences;

class ReferencesMethodCommand extends Command
{
    /**
     * @var ClassMethodReferences
     */
    private $methodReferences;

    /**
     * @var DumperRegistry
     */
    private $dumperRegistry;

    public function __construct(
        ClassMethodReferences $methodReferences,
        DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
        $this->methodReferences = $methodReferences;
        $this->dumperRegistry = $dumperRegistry;
    }

    public function configure()
    {
        $this->setName('references:method');
        $this->setDescription('Find reference to a method');
        $this->addArgument('class', InputArgument::OPTIONAL, 'Class path or FQN');
        $this->addArgument('method', InputArgument::OPTIONAL, 'Method');
        $this->addOption('replace', null, InputOption::VALUE_REQUIRED, 'Replace with this Class FQN');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not write changes to files');
        Handler\FormatHandler::configure($this);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $class = $input->getArgument('class');
        $method = $input->getArgument('method');
        $format = $input->getOption('format');
        $replace = $input->getOption('replace');
        $dryRun = $input->getOption('dry-run');

        $results = $this->methodReferences->findOrReplaceReferences($class, $method, $replace, $dryRun);

        if ($replace && $dryRun) {
            $output->writeln('<info># DRY RUN</> No files will be modified');
        }

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
            Highlight::highlightAtCol($reference['line'], $reference['reference'], $reference['col_no'], $ansi),
            $reference['start'],
            $reference['end'],
        ]);
    }
}

