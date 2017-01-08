<?php

namespace Phpactor\Console\Command\Handler;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Phpactor\CodeContext;
use Phpactor\Util\FileUtil;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class CodeContextHandler
{
    public static function configure(Command $command)
    {
        $command->addOption('offset', null, InputOption::VALUE_OPTIONAL, 0);
        $command->addArgument('fqnOrFname', InputArgument::OPTIONAL, 'Fully qualified class name or filename');
    }

    public static function contextFromInput(InputInterface $input)
    {
        $offset = $input->getOption('offset');
        $name = $input->getArgument('fqnOrFname');

        if ($name) {
            FileUtil::assertExists($name);
            $contents = file_get_contents($name);
        } else {
            $contents = '';
            while ($line = fgets(STDIN)) {
                $contents .= $line;
            }
        }

        return CodeContext::create($name, $contents, (int) $offset);
    }
}
