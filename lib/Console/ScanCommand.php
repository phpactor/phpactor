<?php

namespace Phpactor\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputArgument;
use BetterReflection\Reflection\ReflectionClass;
use BetterReflection\SourceLocator\SingleFileSourceLocator;
use BetterReflection\Reflector\ClassReflector;
use Symfony\Component\Console\Input\InputOption;
use Phpactor\RemoteReflector;

class ScanCommand extends Command
{
    public function configure()
    {
        $this->setName('scan');
        $this->addArgument('path', InputArgument::REQUIRED);
        $this->addOption('bootstrap', 'b', InputOption::VALUE_OPTIONAL);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $nbFiles = 0;
        $nbErrors = 0;
        $finder = new Finder();
        $finder->name('*.php');
        $iterator = $finder->in($input->getArgument('path'));
        $bootstrap = $input->getOption('bootstrap');

        foreach ($iterator as $file) {
            $nbFiles++;
            try {
                $reflector = new RemoteReflector($bootstrap, $file->getPathName());
                $reflector->reflect();
            } catch (\RuntimeException $e) {
                $nbErrors++;
                $output->writeln(sprintf(
                    '<error>ERROR: </error>: %s',
                    $file->getPathName()
                ));
            }
        }

        $output->writeln(sprintf(
            '<info>Done: </info> %s/%s files in %s',
            $nbFiles - $nbErrors,
            $nbFiles,
            number_format(microtime(true) - $start, 4)
        ));
    }
}
