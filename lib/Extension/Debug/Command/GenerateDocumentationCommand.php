<?php

namespace Phpactor\Extension\Debug\Command;

use Phpactor\Extension\Debug\Model\DocumentorRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDocumentationCommand extends Command
{
    private DocumentorRegistry $documentorRegistry;

    private string $documentorName;

    public function __construct(DocumentorRegistry $documentorRegistry, string $documentorName)
    {
        parent::__construct();
        $this->documentorRegistry = $documentorRegistry;
        $this->documentorName = $documentorName;
    }

    protected function configure(): void
    {
        $this->setDescription('Generate configuration reference as an RST document');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $documentor = $this->documentorRegistry->get($this->documentorName);
        fwrite(STDOUT, $documentor->document($this->getName()));

        return 0;
    }
}
