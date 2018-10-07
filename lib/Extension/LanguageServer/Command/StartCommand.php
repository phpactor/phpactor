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
    const NAME = 'server:start';

    /**
     * @var LanguageServerBuilder
     */
    private $languageServerBuilder;

    public function __construct(LanguageServerBuilder $languageServerBuilder)
    {
        parent::__construct();
        $this->languageServerBuilder = $languageServerBuilder;
    }

    protected function configure()
    {
        $this->setName(self::NAME);
        $this->setDescription('EXPERIMENTAL start a Phpactor language server');
        $this->addOption('stdio', null, InputOption::VALUE_NONE);
        $this->addOption('address', null, InputOption::VALUE_REQUIRED, 'Address to start TCP serve', '127.0.0.1:8888');
        $this->addOption('record', null, InputOption::VALUE_REQUIRED, 'Record the incoming requests');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $builder = $this->languageServerBuilder;

        if ($input->getOption('record')) {
            $builder->recordTo($input->getOption('record'));
        }

        if ($input->getOption('stdio')) {
            $builder->stdIoServer();
            $builder->build()->start();
            return 0;
        }

        $builder->tcpServer($input->getOption('address'));

        $output->writeln('<info>Starting TCP server, use -vvv for verbose output</>');

        $server = $builder->build();
        $server->start();
    }
}
