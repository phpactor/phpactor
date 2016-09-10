<?php

namespace Phactor\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Phactor\Knowledge\Storage\Repository;
use Phactor\Knowledge\Reflector\RemoteReflector;

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
        $errors = [];
        $nbFiles = 0;
        $nbErrors = 0;
        $finder = new Finder();
        $finder->name('*.php');
        $iterator = $finder->in($input->getArgument('path'));
        $bootstrap = $input->getOption('bootstrap');

        $repository = new Repository(getcwd() . '/phpactor.sqlite');

        foreach ($iterator as $file) {
            $nbFiles++;
            try {
                $reflector = new RemoteReflector();
                $classHierarchy = $reflector->reflect($file->getPathName(), $bootstrap);
                $repository->storeClassHierachy($classHierarchy);
                $output->write('.');
            } catch (\Exception $e) {
                $nbErrors++;
                $output->write('<error>.</error>');
                $errors[] = $e;
            }

            if ($nbFiles % 80 === 0) {
                $output->writeln('');
            }
        }

        $output->write(PHP_EOL);
        $output->writeln(sprintf(
            '<info>Done: </info> %s/%s files in %s',
            $nbFiles - $nbErrors,
            $nbFiles,
            number_format(microtime(true) - $start, 4)
        ));

        if ($errors) {
            $output->writeln('Errors:');
            foreach ($errors as $error) {
                $output->writeln('  ' . $error->getMessage());
            }
        }
    }
}
