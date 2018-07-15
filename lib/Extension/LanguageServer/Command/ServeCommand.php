<?php

namespace Phpactor\Extension\LanguageServer\Command;

use Phpactor\Extension\LanguageServer\Server\Server;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ServeCommand extends Command
{
    /**
     * @var Server
     */
    private $server;

    public function __construct(Server $server)
    {
        parent::__construct();
        $this->server = $server;
    }

    protected function configure()
    {
        $this->setName('lsp:serve');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('Serving at %s:%s', $this->server->address(), $this->server->port()));
        $this->server->serve();
    }
}
