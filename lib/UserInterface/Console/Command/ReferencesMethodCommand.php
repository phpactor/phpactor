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
use Phpactor\Container\SourceCodeFilesystemExtension;

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
        $this->addOption('risky', null, InputOption::VALUE_NONE, 'Show risky references (matching method with unknown class');
        $this->addOption('replace', null, InputOption::VALUE_REQUIRED, 'Replace with this Class FQN (will not replace riskys)');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not write changes to files');
        Handler\FormatHandler::configure($this);
        Handler\FilesystemHandler::configure($this, SourceCodeFilesystemExtension::FILESYSTEM_GIT);
    }

    public function execute(InputInterface $input, OutputInterface $output, $bar = null)
    {
        if (false) {
            $bar->name();
        }
        $class = $input->getArgument('class');
        $method = $input->getArgument('method');
        $format = $input->getOption('format');
        $replace = $input->getOption('replace');
        $dryRun = $input->getOption('dry-run');
        $risky = $input->getOption('risky');
        $filesystem = $input->getOption('filesystem');

        $results = $this->methodReferences->findOrReplaceReferences($filesystem, $class, $method, $replace, $dryRun);

        if ($replace && $dryRun) {
            $output->writeln('<info># DRY RUN</> No files will be modified');
        }

        if ($format) {
            $this->dumperRegistry->get($format)->dump($output, $results);
            return;
        }

        $output->writeln('<comment># References:</>');
        $count = $this->renderTable($output, $results, 'references', $output->isDecorated());

        if ($risky) {
            $output->write(PHP_EOL);
            $output->writeln('<comment># Risky (unknown classes):</>');
            $riskyCount = $this->renderTable($output, $results, 'risky_references', $output->isDecorated());
        } else {
            $riskyCount = array_reduce($results, function ($acc, $result) {
                return $acc += array_reduce($result, function ($acc, $result) {
                    return $acc += count($result['risky_references']);
                }, 0);
            }, 0);
        }

        if ($replace) {
            $output->write(PHP_EOL);
            $output->writeln('<comment># Replacements:</>');
            $this->renderTable($output, $results, 'replacements', $output->isDecorated());
        }

        $output->write(PHP_EOL);
        $output->writeln(sprintf('%s reference(s), %s risky references', $count, $riskyCount));
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

