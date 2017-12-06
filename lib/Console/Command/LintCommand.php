<?php

namespace Phpactor\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\WorseReflection\Reflector;
use Phpactor\Application\Linter;
use Symfony\Component\Console\Helper\Table;
use Phpactor\WorseReflection\Core\Inference\SymbolInformation;

class LintCommand extends Command
{
    /**
     * @var Linter
     */
    private $linter;

    public function __construct(Linter $linter)
    {
        parent::__construct();
        $this->linter = $linter;
    }

    protected function configure()
    {
        $this->setName('lint');
        $this->addArgument('path');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $problemCollection = $this->linter->lint($input->getArgument('path'));

        foreach ($problemCollection as $path => $problems)
        {
            if ($problems->none()) {
                continue;
            }

            $output->writeln('<info>' . $path . '</>');
            $table = new Table($output);

            /** @var SymbolInformation $problem */
            foreach ($problems as $problem) {

                $table->addRow([
                    $problem->symbol()->position()->start(),
                    $problem->symbol()->position()->end(),
                    implode(', ', $problem->errors()),
                ]);
            }

            $table->render();
        }
    }
}
