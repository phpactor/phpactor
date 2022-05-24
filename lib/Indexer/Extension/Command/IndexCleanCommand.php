<?php

namespace Phpactor\Indexer\Extension\Command;

use Exception;
use InvalidArgumentException;
use Phpactor\Indexer\Model\Index\IndexInfo;
use Phpactor\Indexer\Util\Filesystem as PhpactorFilesystem;
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
    public const CLEAN_ALL = 'clean-all';
    private const INDEX_TO_CLEAN = 'index-to-clean';

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

            DOCS, self::CLEAN_ALL));
        $this->addArgument(self::INDEX_TO_CLEAN, InputArgument::OPTIONAL, 'Index to delete (either the name or the number from the listing)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $indicies = $this->getIndicies();
        $this->renderIndexTable($indicies, $output);

        $argument = $input->getArgument(self::INDEX_TO_CLEAN);
        if ($argument === null && !$input->isInteractive()) {
            return 0;
        }

        if ($argument !== null) {
            $answer = $this->getAnswerFromArgument($indicies, $argument);
        } else {
            $answer = $this->getInteractiveAnswer($indicies, $input, $output);
        }

        $output->writeln('');
        $indiciesToDelete = [];
        if ($answer === null) {
            $indiciesToDelete = $indicies;
        } else {
            $indiciesToDelete = [$indicies[$answer]];
        }

        foreach ($indiciesToDelete as $index) {
            $output->writeln(sprintf('<info>Removing %s</info>', $index->directoryName()));
            $this->filesystem->remove($index->absolutePath());
        }


        return 0;
    }

    /**
     * @param array<IndexInfo> $indexList
     */
    private function getAnswerFromArgument(array $indexList, string $argument): ?int
    {
        if ($argument === self::CLEAN_ALL) {
            return null;
        }

        if (is_numeric($argument) && count($indexList) >= (int) $argument) {
            return ((int) $argument) - 1;
        }

        foreach ($indexList as $i => $index) {
            if ($index->directoryName() === $argument) {
                return $i;
            }
        }

        throw new InvalidArgumentException('Could not find index with argument "'. $argument. '" either use the id of the index or the name from the table above.');
    }

    /**
     * @param array<IndexInfo> $indexList
     */
    private function renderIndexTable(array $indexList, OutputInterface $output): void
    {
        $progressbar = new ProgressBar($output, count($indexList));
        $totalSize = 0;
        $table = new Table($output);
        $table->setHeaders(['#' , 'Directory', 'Size', 'Last modified']);
        foreach ($indexList as $i => $index) {
            /** @var IndexInfo $index */
            $totalSize += $index->size();
            $table->addRow([
                $i + 1,
                $index->directoryName(),
                PhpactorFilesystem::formatSize($index->size()),
                sprintf('%.1f days', $index->lastUpdatedInDays()),
            ]);
            $progressbar->advance();
        }
        $progressbar->finish();
        $output->writeln('');
        $table->render();

        $output->writeln(sprintf('Total size: %s', PhpactorFilesystem::formatSize($totalSize)));
    }

    /**
     * @param array<IndexInfo> $indicies
     */
    private function getInteractiveAnswer(array $indicies, InputInterface $input, OutputInterface $output): ?int
    {
        $indexCount = count($indicies);
        $helper = new QuestionHelper();
        $question = new Question(sprintf(
            "Which index do you want to delete? (1 - %s, %s)\n",
            $indexCount,
            self::CLEAN_ALL
        ));
        $question->setValidator(function (?string $userInput) use ($indexCount) {
            if ($userInput === self::CLEAN_ALL) {
                return null;
            }
            if (!is_numeric($userInput)) {
                throw new Exception('Please provide a number.');
            }

            $number = (int)$userInput;
            if ($number < 0 || $number > $indexCount) {
                throw new Exception('Please provide a number between 1 and '.$indexCount);
            }

            return ((int) $number) - 1;
        });

        return $helper->ask($input, $output, $question);
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
            [IndexInfo::class, 'create'],
            iterator_to_array($finder)
        ));
    }
}
