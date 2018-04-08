<?php

namespace Phpactor\Extension\Core\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Extension\Core\Application\Status;

class StatusCommand extends Command
{
    /**
     * @var Status
     */
    private $status;

    public function __construct(Status $status)
    {
        parent::__construct();
        $this->status = $status;
    }

    protected function configure()
    {
        $this->setName('status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $diagnostics = $this->status->check();

        foreach ($diagnostics['good'] as $good) {
            $output->writeln('<info>✔</> ' . $good);
        }

        foreach ($diagnostics['bad'] as $bad) {
            $output->writeln('<error>✘</> ' . $bad);
        }
    }
}
