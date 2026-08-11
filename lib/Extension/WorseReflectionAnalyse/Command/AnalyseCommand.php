<?php

namespace Phpactor\Extension\WorseReflectionAnalyse\Command;

use Phpactor\Extension\WorseReflectionAnalyse\Model\Analyser;
use Phpactor\TextDocument\ByteOffset;
use Phpactor\TextDocument\LineCol;
use Phpactor\WorseReflection\Core\Diagnostic;
use Phpactor\WorseReflection\Core\Diagnostics;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Path;

class AnalyseCommand extends Command
{
    const ARG_PATH = 'path';
    const OPT_IGNORE_FAILURE = 'ignore-failure';
    const OPT_FORMAT = 'format';

    /**
     * @var array<string,string>
     */
    private array $sources = [];

    public function __construct(private Analyser $analyser)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->setDescription('Experimental diagnostics for files in the given path');
        $this->addArgument(self::ARG_PATH, InputArgument::REQUIRED, 'Path to analyse');
        $this->addOption(self::OPT_FORMAT, null, InputOption::VALUE_REQUIRED, 'Output format ("table" or "json")', 'table');
        $this->addOption(self::OPT_IGNORE_FAILURE, null, InputOption::VALUE_NONE, 'Exit with 0 even if there were problems');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $start = (float)microtime(true);

        $progressOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;

        /**
         * @var array<string,Diagnostics<Diagnostic>> $results
         */
        $results = [];
        $path = $input->getArgument(self::ARG_PATH);

        $count = count(iterator_to_array($this->analyser->fileList($path), true));
        $progressOutput->writeln('Analysing files...');
        $progressOutput->writeln('');
        $progress = new ProgressBar($progressOutput, $count);
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
        $progressOutput->writeln('');
        $progressOutput->writeln('');

        match ($input->getOption(self::OPT_FORMAT)) {
            'json' => $this->renderJson($output, $results),
            default => $this->renderTable($output, $results, $start),
        };

        if ($input->getOption(self::OPT_IGNORE_FAILURE)) {
            return 0;
        }

        return $hasErrors ? 1 : 0;
    }

    /**
     * @param array<string,Diagnostics<Diagnostic>> $results
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
            $table->setHeaders(['line:col', 'severity', 'message']);
            $table->setColumnMaxWidth(2, 60);
            foreach ($diagnostics as $diagnostic) {
                $errorCount++;
                $lineCol = $this->lineCol($file, $diagnostic->range()->start());
                $table->addRow([
                    sprintf('%s:%s', $lineCol->line(), $lineCol->col()),
                    $diagnostic->severity()->toString(),
                    $diagnostic->message(),
                ]);
            }
            $table->render();
            $output->writeln('');
        }
        $output->writeln(sprintf(
            '%s problems in %s seconds with %sb memory',
            number_format($errorCount),
            number_format(microtime(true) - $start, 4),
            number_format(memory_get_peak_usage()),
        ));
    }

    /**
     * @param array<string,Diagnostics<Diagnostic>> $results
     */
    private function renderJson(OutputInterface $output, array $results): void
    {
        foreach ($results as $file => $diagnostics) {
            foreach ($diagnostics as $diagnostic) {
                $lineCol = $this->lineCol($file, $diagnostic->range()->start());
                $output->writeln((string)json_encode([
                    'file' => $file,
                    'line' => $lineCol->line(),
                    'col' => $lineCol->col(),
                    'range' => ['start' => $diagnostic->range()->start()->toInt(), 'end' => $diagnostic->range()->end()->toInt()],
                    'code' => $diagnostic->code(),
                    'message' => $diagnostic->message(),
                    'severity' => $diagnostic->severity()->toString(),
                ], JSON_UNESCAPED_SLASHES));
            }
        }
    }

    private function lineCol(string $file, ByteOffset $offset): LineCol
    {
        if (!isset($this->sources[$file])) {
            $contents = @file_get_contents(Path::makeAbsolute($file, (string)getcwd()));
            $this->sources[$file] = false === $contents ? '' : $contents;
        }

        if ('' === $this->sources[$file]) {
            return new LineCol(1, 1);
        }

        return LineCol::fromByteOffset($this->sources[$file], $offset);
    }
}
