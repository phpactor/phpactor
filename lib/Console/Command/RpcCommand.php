<?php

namespace Phpactor\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RpcCommand extends Command
{
    public function configure()
    {
        $this->setName('rpc');
        $this->setDescription('Execute one or many commands from stdin and receive an imperative response');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
