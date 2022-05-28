<?php

namespace Phpactor\Indexer\Extension\Command;

use Exception;
use Phpactor\Indexer\Model\IndexInfo;
use Phpactor\Indexer\Model\IndexInfos;
use Phpactor\Indexer\Util\Filesystem as PhpactorFilesystem;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class IndexCleanCommand extends Command
{
    public const ARG_INDEX_NAME = 'name';
    public const OPT_CLEAN_ALL = 'all';

    private string $indexDirectory;

    private Filesystem $filesystem;

    public function __construct(string $indexDirectory, Filesystem $filesystem)
    {
        parent::__construct();
        $this->indexDirectory = $indexDirectory;
        $this->filesystem = $filesystem;
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

            Removing all indicies
                bin/console index:clean %s

            === Interactive version ===
            Listing the available indices and asking which ones should be removed
                bin/console index:clean

            DOCS, self::OPT_CLEAN_ALL));
        $this->addArgument(self::ARG_INDEX_NAME, InputArgument::OPTIONAL, 'Index to delete (either the name or the number from the listing)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$output instanceof ConsoleOutput) {
            throw new RuntimeException('Symfony');
        }

        $indexName = $input->getArgument(self::ARG_INDEX_NAME);

        if ($indexName === null && !$input->isInteractive()) {
            return 0;
        }


        $section = $output->section();
        $indicies = $this->getIndicies($section);
        if ($indexName) {
            $this->removeIndex($section, $indicies->get($indexName));
            return 0;
        }
        $removed = null;
        $index = null;
        $section->clear();

        while (true) {
            $this->renderIndexTable($indicies, $section);
            if ($removed && $index) {
                $section->writeln(sprintf('<info>Removed index "%s"</>', $index->name()));
            }
            try {
                // pass $output instead of $section because the interactive input
                // is corrupted with he Section.
                $index = $this->getInteractiveAnswer($indicies, $input, $output);
            } catch (Exception $e) {
                $section->clear();
                $section->writeln(sprintf('<error>%s</>', $e->getMessage()));
                continue;
            }
            if (!$index) {
                break;
            }
            $this->removeIndex($output, $index);
            $indicies = $indicies->remove($index);
            $removed = $index;
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
        $table->render();

        $output->writeln(sprintf('Total size: %s', PhpactorFilesystem::formatSize($totalSize)));
    }

    private function getInteractiveAnswer(IndexInfos $infos, InputInterface $input, OutputInterface $output): ?IndexInfo
    {
        $question = new Question('Index to remove: ', null);
        $question->setAutocompleterValues(array_merge($infos->offsets(),  $infos->names()));
        $result = (new QuestionHelper())->ask($input, $output, $question);

        if (!$result) {
            return null;
        }

        if (is_numeric($result)) {
            return $infos->getByOffset((int)$result);
        }

        return $infos->get((string)$result);
    }

    private function getIndicies(OutputInterface $output): IndexInfos
    {
        $finder = (new Finder())
           ->directories()
           ->in([$this->indexDirectory])
           ->sortByName()
           ->depth('==0')
       ;
        $fileInfos = iterator_to_array($finder);
        $progress = new ProgressBar($output, count($fileInfos));

        $indexes = [];
        foreach ($fileInfos as $fileInfo) {
            $info = IndexInfo::fromFromSplFileInfo($fileInfo);

            // warmup the size
            $info->size();
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
