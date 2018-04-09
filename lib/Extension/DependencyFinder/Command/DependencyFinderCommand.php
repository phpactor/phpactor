<?php

namespace Phpactor\Extension\DependencyFinder\Command;

use Symfony\Component\Console\Command\Command;
use Phpactor\Extension\DependencyFinder\DependencyFinder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Phpactor\Phpactor;

class DependencyFinderCommand extends Command
{
    /**
     * @var DependencyFinder
     */
    private $finder;

    public function __construct(DependencyFinder $finder)
    {
        parent::__construct();
        $this->finder = $finder;
    }

    protected function configure()
    {
        $this->setName('dependency:finder');
        $this->addArgument('path');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        list($poluted, $clean) = $this->finder->detect($path);

        $output->writeln('<comment>Dirty classes:</comment>');
        foreach ($poluted as $file => $dependencies) {
            $output->writeln(sprintf('  <info>%s</> (%s)', Phpactor::relativizePath($file), count($dependencies)));
            foreach ($dependencies as $dependency) {
                $output->writeln('  >> ' . $dependency );
            }
        }

        $output->writeln('<comment>Clean classes:</comment>');
        foreach ($clean as $file) {
            $output->writeln('  ' . Phpactor::relativizePath($file));
        }
    }
}
