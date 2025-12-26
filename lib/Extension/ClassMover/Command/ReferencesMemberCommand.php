<?php

namespace Phpactor\Extension\ClassMover\Command;

use Phpactor\Extension\SourceCodeFilesystem\SourceCodeFilesystemExtension;
use Symfony\Component\Console\Command\Command;
use Phpactor\Extension\Core\Console\Dumper\DumperRegistry;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;
use Phpactor\Phpactor;
use Phpactor\Extension\Core\Console\Formatter\Highlight;
use Phpactor\Extension\ClassMover\Application\ClassMemberReferences;
use Phpactor\Extension\Core\Console\Handler\FormatHandler;
use Phpactor\Extension\Core\Console\Handler\FilesystemHandler;

class ReferencesMemberCommand extends Command
{
    public function __construct(
        private readonly ClassMemberReferences $memberReferences,
        private readonly DumperRegistry $dumperRegistry
    ) {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Find reference to a member');
        $this->addArgument('class', InputArgument::OPTIONAL, 'Class path or FQN');
        $this->addArgument('member', InputArgument::OPTIONAL, 'Method');
        $this->addOption('type', null, InputOption::VALUE_REQUIRED, 'Member type (constant, property or member)');
        $this->addOption('risky', null, InputOption::VALUE_NONE, 'Show risky references (matching member with unknown class');
        $this->addOption('replace', null, InputOption::VALUE_REQUIRED, 'Replace with this Class FQN (will not replace riskys)');
        $this->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not write changes to files');
        FormatHandler::configure($this);
        FilesystemHandler::configure($this, SourceCodeFilesystemExtension::FILESYSTEM_GIT);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $class = $input->getArgument('class');
        $member = $input->getArgument('member');
        $format = $input->getOption('format');
        $replace = $input->getOption('replace');
        $dryRun = (bool) $input->getOption('dry-run');
        $risky = (bool) $input->getOption('risky');
        $memberType = $input->getOption('type');
        $filesystem = $input->getOption('filesystem');

        $results = $this->memberReferences->findOrReplaceReferences(
            $filesystem,
            $class,
            $member,
            $memberType,
            $replace,
            $dryRun
        );

        if ($replace && $dryRun) {
            $output->writeln('<info># DRY RUN</> No files will be modified');
        }

        if ($format) {
            $this->dumperRegistry->get($format)->dump($output, $results);
            return 0;
        }

        $output->writeln('<comment># References:</>');
        $count = $this->renderTable($output, $results, 'references', $output->isDecorated());

        if ($risky) {
            $output->write("\n");
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
            $output->write("\n");
            $output->writeln('<comment># Replacements:</>');
            $this->renderTable($output, $results, 'replacements', $output->isDecorated());
        }

        $output->write("\n");
        $output->writeln(sprintf('%s reference(s), %s risky references', $count, $riskyCount));

        return 0;
    }

    private function renderTable(OutputInterface $output, array $results, string $type, bool $ansi): int
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
