<?php

namespace Phpactor\Extension\ExtensionManager\Command;

use Phpactor\Extension\ExtensionManager\Model\Extension;
use Phpactor\Extension\ExtensionManager\Service\ExtensionLister;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Command
{
    /**
     * @var ExtensionLister
     */
    private $lister;

    public function __construct(ExtensionLister $lister)
    {
        parent::__construct();
        $this->lister = $lister;
    }

    protected function configure(): void
    {
        $this->setDescription('List extensions');
        $this->addOption('installed', null, InputOption::VALUE_NONE, 'Only show installed packages');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $onlyInstalled = $input->getOption('installed');
        assert(is_bool($onlyInstalled));
        $table = new Table($output);
        $table->setHeaders([
            'Name',
            'Version',
            'Description',
        ]);

        foreach ($this->lister->list($onlyInstalled)->sorted() as $extension) {
            $table->addRow([
                $extension->name(),
                $this->formatVersion($extension),
                $extension->description()
            ]);
        }
        $table->render();
        $output->writeln('<comment>* core extension</>');

        return 0;
    }

    private function formatVersion(Extension $extension): string
    {
        if (!$extension->state()->isInstalled()) {
            return '';
        }

        $version = $extension->version();

        if ($extension->state()->isSecondary()) {
            return $version;
        }

        return sprintf('<options=bold>%s*</>', $version);
    }
}
