<?php

namespace Phpactor\Extension\LanguageServer\Command;

use Phpactor\LanguageServer\Core\Server;
use Phpactor\LanguageServer\LanguageServerBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StartCommand extends Command
{
    /**
     * @var LanguageServerBuilder
     */
    private $languageServerBuilder;

    /**
     * @var array
     */
    private $handlerNames;


    public function __construct(LanguageServerBuilder $languageServerBuilder, array $handlerNames)
    {
        parent::__construct();
        $this->languageServerBuilder = $languageServerBuilder;
        $this->handlerNames = $handlerNames;
    }

    protected function configure()
    {
        $this->setName('server:start');
        $this->setDescription('EXPERIMENTAL start a Phpactor language server');
        $this->addOption('stdio', null, InputOption::VALUE_NONE);
        $this->addOption('address', null, InputOption::VALUE_REQUIRED, 'Address to start TCP serve', '127.0.0.1:8888');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $builder = $this->languageServerBuilder;

        if ($input->getOption('stdio')) {
            $builder->stdIoServer();
            $builder->build()->start();
            return 0;
        }

        $builder->tcpServer($input->getOption('address'));

        $output->writeln('<info>Starting TCP server, use -vvv for verbose output</>');
        $output->writeln('<info>Registered Phpactor handlers:</>: ' . implode(', ', $this->handlerNames));

        $server = $builder->build();
        $server->start();
    }
}
