<?php

namespace Phpactor\Console\Dumper;

use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Console\Dumper\Dumper;

final class JsonDumper implements Dumper
{
    public function dump(OutputInterface $output, array $data)
    {
        $output->writeln(json_encode($data));
    }
}
