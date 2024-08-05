<?php

namespace Phpactor\Indexer\Extension\Command;

use Exception;
use Phpactor\Indexer\Model\IndexInfo;
use Phpactor\Indexer\Model\IndexInfos;
use Phpactor\Indexer\Model\IndexLister;
use Phpactor\Indexer\Util\Filesystem as PhpactorFilesystem;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

class IndexCleanCommand extends Command
{
    public const ARG_INDEX_NAME = 'name';
    public const OPT_CLEAN_ALL = 'all';

    public function __construct(private IndexLister $indexLister, private Filesystem $filesystem)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Removing a project index from the cache');
        $this->setHelp(sprintf(<<<DOCS
            === Non interactive version ===
            Listing the available indices
                bin/console index:clean --no-interaction

            Removing an index by name
                bin/console index:clean <index name>

            Removing an index by the number in the list view:
                bin/console index:clean <index number>

            Removing all indices
                bin/console index:clean %s

            === Interactive version ===
            Listing the available indices and asking which ones should be removed
                bin/console index:clean

            DOCS, self::OPT_CLEAN_ALL));
        $this->addArgument(self::ARG_INDEX_NAME, InputArgument::IS_ARRAY, 'Index names to delete');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$output instanceof ConsoleOutput) {
            throw new RuntimeException('Symfony');
        }

        $indexNames = $input->getArgument(self::ARG_INDEX_NAME);

        // Non interactive commands with no index to delete should do nothing
        if (count($indexNames) === 0 && !$input->isInteractive()) {
            return 0;
        }

        $section = $output->section();
        $indices = $this->getIndices($section);
        if (count($indexNames) !== 0) {
            if ($indexNames[0] === self::OPT_CLEAN_ALL) {
                foreach ($indices as $index) {
                    $this->removeIndex($output, $index);
                }
                return 0;
            }
            foreach ($indexNames as $indexName) {
                $this->removeIndex($section, $indices->get($indexName));
            }
            return 0;
        }
        $index = null;
        $section->clear();

        while (true) {
            $this->renderIndexTable($indices, $section);
            if ($index) {
                $section->writeln(sprintf('<info>Removed index "%s"</>', $index->name()));
            }
            try {
                // pass $output instead of $section because the interactive input
                // is corrupted with he Section.
                $index = $this->getInteractiveAnswer($indices, $input, $output);
            } catch (Exception $e) {
                $section->clear();
                $section->writeln(sprintf('<error>%s</>', $e->getMessage()));
                continue;
            }
            if (!$index) {
                break;
            }

            if ($index->absolutePath() === self::OPT_CLEAN_ALL) {
                foreach ($indices as $index) {
                    $this->removeIndex($output, $index);
                }
                break;
            }

            $this->removeIndex($output, $index);
            $indices = $indices->remove($index);
            $section->clear();
        }

        return 0;
    }

    private function renderIndexTable(IndexInfos $indexList, OutputInterface $output): void
    {
        $totalSize = 0;
        $table = new Table($output);
        $table->setHeaders(['#' , 'Directory', 'Size', 'Age', 'Modified']);
        $offset = 1;
        foreach ($indexList as $index) {
            $totalSize += $index->size();
            $table->addRow([
                $offset++,
                $index->name(),
                PhpactorFilesystem::formatSize($index->size()),
                sprintf('%.1f days', $index->ageInDays()),
                sprintf('%.1f days', $index->lastModifiedInDays()),
            ]);
        }
        $table->addRow(new TableSeparator());
        $table->addRow(['Σ', self::OPT_CLEAN_ALL, PhpactorFilesystem::formatSize($indexList->totalSize()), '', '']);
        $table->render();

        $output->writeln(sprintf('Total size: %s', PhpactorFilesystem::formatSize($totalSize)));
    }

    private function getInteractiveAnswer(IndexInfos $infos, InputInterface $input, OutputInterface $output): ?IndexInfo
    {
        $question = new Question('Index to remove: ', null);
        $question->setAutocompleterValues(array_merge($infos->offsets(), $infos->names(), [self::OPT_CLEAN_ALL]));
        $result = (new QuestionHelper())->ask($input, $output, $question);

        if (!$result) {
            return null;
        }

        if ($result === self::OPT_CLEAN_ALL) {
            return new IndexInfo(self::OPT_CLEAN_ALL, '', 0, 0, 0);
        }

        if (is_numeric($result)) {
            return $infos->getByOffset((int)$result);
        }

        return $infos->get((string)$result);
    }

    private function getIndices(OutputInterface $output): IndexInfos
    {
        $indexes = [];
        $progress = new ProgressBar($output);
        foreach ($this->indexLister->list() as $info) {
            $indexes[] = $info;
            $progress->advance();
        }
        $progress->finish();

        return new IndexInfos($indexes);
    }

    private function removeIndex(OutputInterface $output, IndexInfo $index): void
    {
        $output->writeln(sprintf('<info>Removing %s</info>', $index->name()));
        $this->filesystem->remove($index->absolutePath());
    }
}
