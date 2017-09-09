<?php

namespace Phpactor\Console\Dumper;

use Symfony\Component\Console\Output\OutputInterface;
use Phpactor\Console\Dumper\Dumper;

/**
 * Passthru dumper - just write the array data to the output.
 * Used by the RPC system
 */
final class PassthruDumper implements Dumper
{
    public function dump(OutputInterface $output, array $data)
    {
        $output->write($data);
    }
}
