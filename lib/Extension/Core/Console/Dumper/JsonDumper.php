<?php

namespace Phpactor\Extension\Core\Console\Dumper;

use Symfony\Component\Console\Output\OutputInterface;

final class JsonDumper implements Dumper
{
    public function dump(OutputInterface $output, array $data): void
    {
        $output->writeln(json_encode($data));
    }
}
