<?php

namespace Phpactor\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Phpactor\Application\Transformer;

class ClassTransformCommand extends Command
{
    /**
     * @var Transformer
     */
    private $transformer;

    public function __construct(
        Transformer $transformer
    ) {
        parent::__construct();
        $this->transformer = $transformer;
    }

    public function configure()
    {
        $this->setName('class:transform');
        $this->setDescription('Apply a transformation to an existing class (path or FQN)');
        $this->addArgument('src', InputArgument::REQUIRED, 'Source path or FQN');
        $this->addOption('transform', 't', InputOption::VALUE_REQUIRED|InputOption::VALUE_IS_ARRAY, 'Tranformations to apply', []);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $src = $input->getArgument('src');
        $transformations = $input->getOption('transform');

        $output->writeln($this->transformer->transform($src, $transformations));
    }
}
