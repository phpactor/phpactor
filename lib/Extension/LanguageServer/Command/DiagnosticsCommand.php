<?php

namespace Phpactor\Extension\LanguageServer\Command;

use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiagnosticsCommand extends Command
{
    public const NAME = 'language-server:diagnostics';

    public function __construct(private DiagnosticsProvider $provider)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Internal: resolve diagnostics in JSON for document provided over STDIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
    }
}
