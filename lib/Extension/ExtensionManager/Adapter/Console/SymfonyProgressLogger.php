<?php

namespace Phpactor\Extension\ExtensionManager\Adapter\Console;

use Phpactor\Extension\ExtensionManager\Service\ProgressLogger;
use Symfony\Component\Console\Output\OutputInterface;

class SymfonyProgressLogger implements ProgressLogger
{
    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function log(string $message): void
    {
        $this->output->writeln($message);
    }
}
