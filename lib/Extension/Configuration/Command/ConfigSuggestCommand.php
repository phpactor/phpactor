<?php

namespace Phpactor\Extension\Configuration\Command;

use Exception;
use Phpactor\Configurator\Configurator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class ConfigSuggestCommand extends Command
{
    public function __construct(private Configurator $configurator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Suggest configuration changes based on current project');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        assert($output instanceof ConsoleOutput);
        $question = new QuestionHelper();
        $nbChanges = 0;
        foreach ($this->configurator->suggestChanges() as $change) {
            $apply = $question->ask($input, $output, new ConfirmationQuestion($change->prompt()));
            try {
                $this->configurator->apply($change, $apply);
                $nbChanges++;
            } catch (Exception $e) {
                $output->writeln(sprintf('<error>Could not apply change: </error>: %s', $e->getMessage()));
            }
        }

        $output->getErrorOutput()->writeln(sprintf('%d changes applied', $nbChanges));

        return 0;
    }
}
