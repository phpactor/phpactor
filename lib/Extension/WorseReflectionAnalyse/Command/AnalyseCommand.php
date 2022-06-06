<?php

namespace Phpactor\Extension\WorseReflectionAnalyse\Command;

use Phpactor\Extension\WorseReflectionAnalyse\Model\Analyser;
use Phpactor\WorseReflection\Core\Diagnostics;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
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
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * @var array<string,Diagnostics> $results
         */
        $results = [];
        foreach ($this->analyser->analyse($input->getArgument(self::ARG_PATH)) as $file => $diagnostics) {
            $results[$file] = $diagnostics;
        }

        $hasErrors = false;
        $count = 0;

        foreach ($results as $file => $diagnostics) {
            if (!count($diagnostics)) {
                continue;
            }
            $hasErrors = true;
            $output->writeln($file);
            $table = new Table($output);
            $table->setHeaders(['position', 'severity', 'message']);
            $table->setColumnMaxWidth(2, 60);
            foreach ($diagnostics as $diagnostic) {
                $count++;
                $table->addRow([
                    sprintf('%s:%s', $diagnostic->range()->start()->toInt(), $diagnostic->range()->end()->toInt()),
                    $diagnostic->severity()->toString(),
                    $diagnostic->message(),
                ]);
            }
            $table->render();
            $output->writeln('');
        }

        $output->writeln(sprintf('Found %s issues', $count));

        return $hasErrors ? 1 : 0;
    }
}
