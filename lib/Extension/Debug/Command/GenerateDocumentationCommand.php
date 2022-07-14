<?php

namespace Phpactor\Extension\Debug\Command;

use Phpactor\Extension\Debug\Model\Documentor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDocumentationCommand extends Command
{
    private Documentor $documentor;

    public function __construct(Documentor $documentor)
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
        fwrite(STDOUT, $this->documentor->document($this->getName()));
        return 0;
    }
}
