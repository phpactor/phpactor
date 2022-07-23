<?php

namespace Phpactor\Extension\Debug\Command;

use Phpactor\Extension\Debug\Model\DocumentorRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDocumentationCommand extends Command
{
    private DocumentorRegistry $documentorRegistry;

    public function __construct(DocumentorRegistry $documentorRegistry)
    {
        parent::__construct();
        $this->documentorRegistry = $documentorRegistry;
    }

    protected function configure(): void
    {
        $this->setDescription('Generate configuration reference as an RST document');
        $this->addArgument('documentor', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $documentor = $this->documentorRegistry->get($input->getArgument('documentor'));
        fwrite(STDOUT, $documentor->document($this->getName()));

        return 0;
    }
}
