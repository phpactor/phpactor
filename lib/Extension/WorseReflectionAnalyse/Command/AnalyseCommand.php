<?php

namespace Phpactor\Extension\WorseReflectionAnalyse\Command;

use Phpactor\Extension\WorseReflectionAnalyse\Model\Analyser;
use Phpactor\WorseReflection\Core\Diagnostics;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyseCommand extends Command
{
    const ARG_PATH = 'path';

    private Analyser $analyser;

    public function __construct(Analyser $analyser)
    {
        parent::__construct();

        $this->analyser = $analyser;
    }

    public function configure(): void
    {
        $this->setDescription('Experimental diagnostics for files in the given path');
        $this->addArgument(self::ARG_PATH, InputArgument::REQUIRED, 'Path to analyse');
        $this->addOption('format', null, InputOption::VALUE_REQUIRED, 'json');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $start = (float)microtime(true);

        /**
         * @var array<string,Diagnostics> $results
         */
        $results = [];
        $path = $input->getArgument(self::ARG_PATH);

        $count = count(iterator_to_array($this->analyser->fileList($path), true));
        $output->writeln('Analysing files...');
        $output->writeln('');
        $progress = new ProgressBar($output, $count);
        $progress->start();
        $hasErrors = false;

        foreach ($this->analyser->analyse($path) as $file => $diagnostics) {
            $progress->advance();
            $results[$file] = $diagnostics;
            if (0 !== $diagnostics->count()) {
                $hasErrors = true;
            }
        }
        $progress->finish();
        $output->writeln('');
        $output->writeln('');

        switch ($input->getOption('format')) {
            case 'json':
                $this->renderJson($output, $results);
                break;
            default:
                $this->renderTable($output, $results, $start);
        }


        return $hasErrors ? 1 : 0;
    }

    /**
     * @param array<string,Diagnostics> $results
     */
    private function renderTable(OutputInterface $output, array $results, float $start): void
    {
        $errorCount = 0;
        foreach ($results as $file => $diagnostics) {
            if (!count($diagnostics)) {
                continue;
            }
            $output->writeln($file);
            $table = new Table($output);
            $table->setHeaders(['range', 'severity', 'message']);
            $table->setColumnMaxWidth(2, 60);
            foreach ($diagnostics as $diagnostic) {
                $errorCount++;
                $table->addRow([
                    sprintf('%s:%s', $diagnostic->range()->start()->toInt(), $diagnostic->range()->end()->toInt()),
                    $diagnostic->severity()->toString(),
                    $diagnostic->message(),
                ]);
            }
            $table->render();
            $output->writeln('');
        }
        $output->writeln(sprintf('%s problems in %s seconds', $errorCount, number_format(microtime(true) - $start, 4)));
    }

    /**
     * @param array<string,Diagnostics> $results
     */
    private function renderJson(OutputInterface $output, array $results): void
    {
        foreach ($results as $file => $diagnostics) {
            foreach ($diagnostics as $diagnostic) {
                $output->writeln((string)json_encode([
                    'file' => $file,
                    'range' => ['start' => $diagnostic->range()->start()->toInt(), 'end' => $diagnostic->range()->end()->toInt()],
                    'message' => $diagnostic->message(),
                    'severity' => $diagnostic->severity(),
                ]));
            }
        }
    }
}
