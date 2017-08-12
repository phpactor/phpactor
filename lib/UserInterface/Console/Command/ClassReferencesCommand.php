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

        if ($replace) {
            $results = $this->referenceFinder->replaceReferences($class, $replace, $input->getOption('dry-run'));
        } else {
            $results = $this->referenceFinder->findReferences($class);
        }


        $format = $input->getOption('format');

        if ($format) {
            $this->dumperRegistry->get($format)->dump($output, $results);
            return;
        }

        $this->renderTable($output, $results);
    }

    private function renderTable(OutputInterface $output, array $results)
    {
        $table = new Table($output);
        $table->setHeaders([
            'Path',
            'LN',
            'Line',
            'OS',
            'OE',
        ]);

        foreach ($results['references'] as $result) {
            foreach ($result['references'] as $reference) {
                $table->addRow([
                    Phpactor::relativizePath($result['file']),
                    $reference['line_no'],
                    $this->formatLine($reference['line'], $reference['reference'], $reference['start'], $reference['end']),
                    $reference['start'],
                    $reference['end'],
                ]);
            }
        }

        $table->render();
    }

    private function formatLine(string $line, string $reference)
    {
        return str_replace($reference, '<bright>' . $reference . '</>', $line);
    }
}

