<?php

namespace Phpactor\Extension\Console;

use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;
use Symfony\Component\Console\Command\Command;

class PhpactorCommandLoader extends ContainerCommandLoader
{
    public function get(string $name): Command
    {
        $command = parent::get($name);
        $command->setName($name);

        return $command;
    }
}
