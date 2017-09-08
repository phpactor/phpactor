<?php

namespace Phpactor\Console\Command\Handler;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

class FormatHandler
{
    public static function configure(Command $command)
    {
        $command->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format');
    }
}
