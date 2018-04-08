<?php

namespace Phpactor\Extension\Core\Console\Dumper;

use Symfony\Component\Console\Output\OutputInterface;

interface Dumper
{
    public function dump(OutputInterface $output, array $data);
}
