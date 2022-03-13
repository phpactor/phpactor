<?php

namespace Phpactor\Extension\Debug\Command;

use Phpactor\Extension\Debug\Model\ExtensionDocumentor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DocumentExtensionsCommand extends Command
{
    private ExtensionDocumentor $documentor;

    public function __construct(ExtensionDocumentor $documentor)
    {
        parent::__construct();
        $this->documentor = $documentor;
    }

    protected function configure(): void
    {
        $this->setDescription('Generate configuration reference as an RST document');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        fwrite(STDOUT, $this->documentor->document());
        return 0;
    }
}
