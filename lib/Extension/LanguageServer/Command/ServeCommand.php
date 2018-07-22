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
        $this->addOption('save-requests-to', null, InputOption::VALUE_REQUIRED, 'File to store requests in');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $this->serverFactory->create([
            'info-message-callback' => function (string $message) use ($output) {
                $output->writeln($message);
            },
            'save-requests-to-file' => $input->getOption('save-requests-to'),
        ]);
        $saveFile = $input->getOption('save-requests-to');
        $server->serve();
    }
}
