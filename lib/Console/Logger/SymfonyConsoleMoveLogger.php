<?php

namespace Phpactor\Console\Logger;

use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Application\ClassMover\MoveLogger;
use DTL\ClassMover\RefFinder\FullyQualifiedName;
use DTL\ClassMover\Finder\FilePath;
use Phpactor\Phpactor;

class SymfonyConsoleMoveLogger implements MoveLogger
{
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function moving(string $srcPath, string $destPath)
    {
        $this->output->writeln(sprintf(
            'Moving <info>%s</> <comment>=></> <info>%s</>',
            Phpactor::relativizePath($srcPath), Phpactor::relativizePath($destPath)
        ));
    }

    public function replacing(FullyQualifiedName $src, FullyQualifiedName $dest, FilePath $path)
    {
        $this->output->writeln(sprintf(
            '[REP] <comment>%s</>: %s <info>=></> %s',
            Phpactor::relativizePath($path->__toString()),
            $src->__toString(),
            $dest->__toString()
        ));
    }
}
