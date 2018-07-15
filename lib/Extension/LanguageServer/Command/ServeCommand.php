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
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $port;

    public function __construct(string $address, string $port)
    {
        $this->address = $address;
        $this->port = $port;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('lsp:serve');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(sprintf('Serving at %s:%s', $this->address, $this->port));
        $server = new Server($this->address, $this->port);
        $server->serve();
    }
}
