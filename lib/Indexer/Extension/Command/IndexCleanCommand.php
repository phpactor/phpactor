<?php

namespace Phpactor\Indexer\Extension\Command;

use Phpactor\Indexer\Model\Index\IndexInfo;
use Phpactor\Indexer\Util\Filesystem as PhpactorFilesystem;
use SplFileInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
        $indicies = $this->getIndicies();
        $this->renderIndexTable($indicies, $output);

        $argument = $input->getArgument(self::ARG_INDEX_NAME);
        if ($argument === null && !$input->isInteractive()) {
            return 0;
        }

        $answer = $argument;
        if ($answer === null) {
            $answer = $this->getInteractiveAnswer(count($indicies), $input, $output);
        }

        $output->writeln('');

        foreach ($this->getIndiciesToDelete($indicies, $answer) as $index) {
            $output->writeln(sprintf('<info>Removing %s</info>', $index->directoryName()));
            $this->filesystem->remove($index->absolutePath());
        }


        return 0;
    }

    /**
     * @param array<IndexInfo> $indecies
     *
     * @return array<IndexInfo>
     */
    private function getIndiciesToDelete(array $indecies, string $answer): array
    {
        $indeciesToDelete = [];
        if ($answer === self::OPT_CLEAN_ALL) {
            return $indecies;
        }

        $indexCount = count($indecies);
        foreach (explode(',', $answer) as $indexString) {
            // Trying to find the index by number
            if (is_numeric($indexString)) {
                if ($indexCount >= (int) $indexString) {
                    $indexToDelete = ((int) $indexString) - 1;

                    $indeciesToDelete[] = $indecies[$indexToDelete];
                }
            } else {
                // Finding the index by name
                foreach ($indecies as $index) {
                    if ($index->directoryName() === $indexString) {
                        $indeciesToDelete[] = $index;
                        break;
                    }
                }
            }
        }

        return $indeciesToDelete;
    }

    /**
     * @param array<IndexInfo> $indexList
     */
    private function renderIndexTable(array $indexList, OutputInterface $output): void
    {
        $progressbar = new ProgressBar($output, count($indexList));
        $totalSize = 0;
        $table = new Table($output);
        $table->setHeaders(['#' , 'Directory', 'Size', 'Age', 'Modified']);
        foreach ($indexList as $i => $index) {
            /** @var IndexInfo $index */
            $totalSize += $index->size();
            $table->addRow([
                $i + 1,
                $index->directoryName(),
                PhpactorFilesystem::formatSize($index->size()),
                sprintf('%.1f days', $index->ageInDays()),
                sprintf('%.1f days', $index->lastModifiedInDays()),
            ]);
            $progressbar->advance();
        }
        $progressbar->finish();
        $output->writeln('');
        $table->render();

        $output->writeln(sprintf('Total size: %s', PhpactorFilesystem::formatSize($totalSize)));
    }

    private function getInteractiveAnswer(int $indexCount, InputInterface $input, OutputInterface $output): string
    {
        $all= self::OPT_CLEAN_ALL;

        $question = new Question(
            <<<QUESTION
                Which index do you want to delete? (1 - $indexCount, $all)
                If you want to delete multiple indexes provide all of their names or numbers comma separated.

                Default: none
                >
                QUESTION,
            ''
        );

        return (new QuestionHelper())->ask($input, $output, $question);
    }

    /**
     * @return array<IndexInfo>
     */
    private function getIndicies(): array
    {
        $finder = (new Finder())
           ->directories()
           ->in([$this->indexDirectory])
           ->sortByName()
           ->depth('==0')
       ;
        return array_values(array_map(
            fn (SplFileInfo $info) => IndexInfo::fromSplFileInfo($info),
            iterator_to_array($finder)
        ));
    }
}
