<?php declare(ticks=1);

namespace Phpactor\Extension\LanguageServer\Command;

use Phpactor\Extension\LanguageServer\Server\Server;
use Phpactor\Extension\LanguageServer\Server\ServerFactory;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServeCommand extends Command
{
    /**
     * @var ServerFactory
     */
    private $serverFactory;

    public function __construct(ServerFactory $serverFactory)
    {
        parent::__construct();
        $this->serverFactory = $serverFactory;
    }

    protected function configure()
    {
        $this->setName('lsp:serve');
        $this->setDescription('Start a Language Server Protocol server');
        $this->setHelp(<<<'EOT'
Start a Phpactor Language Server which understands the Language Server Protocol:

    https://microsoft.github.io/language-server-protocol/specification

Currently the server only supports communication over STDIN/STDOUT.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $this->serverFactory->create();
        $server->serve();
    }
}
