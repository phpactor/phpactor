<?php

namespace Phpactor\UserInterface\Console\Command\Handler;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

class FormatHandler
{
    const FORMAT_JSON = 'json';
    const FORMAT_CONSOLE = 'console';

    const VALID_FORMATS = [
        self::FORMAT_JSON,
        self::FORMAT_CONSOLE
    ];

    public static function configure(Command $command)
    {
        $command->addOption('format', null, InputOption::VALUE_REQUIRED, sprintf(
            'Output format: "%s"', implode('", "', self::VALID_FORMATS)
        ), 'console');
    }
}
