<?php

namespace Phpactor\Extension\ExtensionManager\Command;

use Phpactor\Extension\ExtensionManager\Service\InstallerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
    /**
     * @var InstallerService
     */
    private $installer;

    public function __construct(InstallerService $installer)
    {
        parent::__construct();
        $this->installer = $installer;
    }

    protected function configure(): void
    {
        $this->setDescription('Update extensions');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->installer->installForceUpdate();

        return 0;
    }
}
