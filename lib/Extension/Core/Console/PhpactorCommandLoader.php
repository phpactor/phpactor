<?php

namespace Phpactor\Extension\Core\Console;

use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

class PhpactorCommandLoader extends ContainerCommandLoader
{
    public function get($name)
    {
        $command = parent::get($name);
        $command->setName($name);

        return $command;
    }
}
