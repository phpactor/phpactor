<?php

namespace Phpactor\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Phactor\Knowledge\Storage\Repository;
use Phactor\Knowledge\Reflector\RemoteReflector;
use Phpactor\Reflection\ReflectorInterface;
use Phpactor\Storage\Storage;
use Phpactor\Reflection\Exception\ReflectionException;
use BetterReflection\Reflector\ClassReflector;

class ScanCommand extends Command
{
    private $storage;

    /**
     * @var ClassReflector
     */
    private $reflector;

    private $config;

    public function __construct(Storage $storage, ClassReflector $reflector)
    {
        parent::__construct();
        $this->storage = $storage;
        $this->reflector = $reflector;
    }

    public function configure()
    {
        $this->setName('scan');
        $this->addArgument('path', InputArgument::REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $verbose = $input->getOption('verbose');
        $start = microtime(true);
        $errors = [];
        $nbFiles = 0;
        $nbErrors = 0;

        $finder = new Finder();
        $finder->name('*.php');
        $finder->files()->filter(function (\SplFileInfo $info) {
            return !preg_match('{vendor/.*/[tT]ests/}', $info->getPathName());
        });

        $iterator = $finder->in($input->getArgument('path'));
        $total = count($finder);
        $output->writeln(sprintf('Indexing "%s" files', $total));

        foreach ($iterator as $file) {
            $nbFiles++;
            try {
                $classReflection = $this->reflector->reflectFile($file->getPathName());
                $this->storage->persistClass($classReflection);
                $output->write('.');
            } catch (\Exception $e) {
                $nbErrors++;
                $output->write('<error>.</error>');
                $errors[] = $e;
            }

            if ($nbFiles % 80 === 0) {
                $output->writeln(sprintf(' (%s/%s)', $nbFiles, $total));
            }
        }

        $output->write(PHP_EOL);
        $output->writeln('Flushing storage');
        $this->storage->flush();

        $output->write(PHP_EOL);
        $output->writeln(sprintf(
            '<info>Done: </info> %s/%s files in %s',
            $nbFiles - $nbErrors,
            $nbFiles,
            number_format(microtime(true) - $start, 4)
        ));

        if ($verbose && $errors) {
            $output->writeln('Errors:');
            foreach ($errors as $error) {
                $output->writeln('  ' . $error->getMessage());
            }
        }
    }
}
