<?php

namespace Phpactor;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Incomplete extends Command
{
    public function configure()
    {
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->foobar->barf
    }

    public function foobar()
    {
    }
}

