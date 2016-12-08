<?php

namespace Phpactor\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Index\Indexer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\ProgressBar;
use Phpactor\Index\Index;

class IndexCommand extends Command
{
    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var Finder
     */
    private $finder;

    public function __construct(Indexer $indexer, Finder $finder = null)
    {
        parent::__construct();
        $this->indexer = $indexer;
        $this->finder = $finder ?: new Finder();
    }

    public function configure()
    {
        $this->setName('index');
        $this->addOption('path', null, InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'Path to index');
        $this->addOption('index', null, Inputoption::VALUE_REQUIRED, 'Path to index file');
        $this->addOption('exclude', null, Inputoption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'Regex pattern to exclude');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $paths = $input->getOption('path');
        $indexPath = $input->getOption('index') ?: getcwd() . '/.phpactor.php';
        $excludes = $input->getOption('exclude');

        $index = new Index($paths);
        if (file_exists($indexPath)) {
            $existingIndex = Index::loadFromFile($indexPath);

            if ($existingIndex->getPaths() == $paths) {
                $this->finder->date('> ' . date('c', $existingIndex->getTimestamp()));
                $index->setMap($existingIndex->getMap());;
            }
        }

        if (empty($paths)) {
            throw new \InvalidArgumentException(
                'You must provide at least one path'
            );
        }

        $this->finder->files();
        $this->finder->in($paths);

        foreach ($excludes as $exclude) {
            $this->finder->notPath($exclude);
        }

        $progress = new ProgressBar($output, count($this->finder));
        $progress->start();

        $index = $this->indexer->__invoke($index, $this->finder, function ($file) use ($progress, $output) {
            $progress->advance();
        });

        $progress->finish();

        file_put_contents($indexPath, "<?php return <<<'EOT'" . PHP_EOL . serialize($index) . PHP_EOL . 'EOT' . PHP_EOL . ';');
    }
}
