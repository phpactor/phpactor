<?php

namespace Phpactor\Extension\Console;

use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

class PhpactorCommandLoader extends ContainerCommandLoader
{
    public function get(string $name)
    {
        $command = parent::get($name);
        $command->setName($name);

        return $command;
    }
}
