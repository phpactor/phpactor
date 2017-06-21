<?php

namespace Phpactor\Console\Logger;

use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Application\ClassMover\MoveLogger;
use DTL\ClassMover\RefFinder\FullyQualifiedName;
use Phpactor\Phpactor;
use DTL\Filesystem\Domain\FilePath;

class SymfonyConsoleMoveLogger implements MoveLogger
{
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function moving(FilePath $srcPath, FilePath $destPath)
    {
        $this->output->writeln(sprintf(
            '<info>[MOVE]</info> %s <comment>=></> %s',
            $srcPath->relativePath(), $destPath->relativePath()
        ));
    }

    public function replacing(FullyQualifiedName $src, FullyQualifiedName $dest, FilePath $path)
    {
        $this->output->writeln(sprintf(
            '<info>[REPL]</info> <comment>%s</>: %s <info>=></> %s',
            $path->relativePath(),
            $src->__toString(),
            $dest->__toString()
        ));
    }
}
