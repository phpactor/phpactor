<?php

namespace Phpactor\Extension\LanguageServer\Command;

use Phpactor\LanguageServer\LanguageServerBuilder;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class StartCommand extends Command
{
    public const NAME = 'language-server';

    private const OPT_ADDRESS = 'address';
    private const OPT_NO_LOOP = 'no-loop';


    /**
     * @var LanguageServerBuilder
     */
    private $languageServerBuilder;

    public function __construct(LanguageServerBuilder $languageServerBuilder)
    {
        parent::__construct();
        $this->languageServerBuilder = $languageServerBuilder;
    }

    protected function configure(): void
    {
        $this->setDescription('Start Language Server');
        $this->addOption(self::OPT_ADDRESS, null, InputOption::VALUE_REQUIRED, 'Start a TCP server at this address (e.g. 127.0.0.1:0)');
        $this->addOption(self::OPT_NO_LOOP, null, InputOption::VALUE_NONE, 'Do not run the event loop (debug)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $address = $input->getOption(self::OPT_ADDRESS);
        $noLoop = (bool)$input->getOption(self::OPT_NO_LOOP);

        $builder = $this->languageServerBuilder;

        $this->logMessage($output, '<info>Starting language server, use -vvv for verbose output</>');

        if ($address && is_string($address)) {
            $this->configureTcpServer($address, $builder);
        }

        $server = $builder->build();

        if ($noLoop) {
            $server->start();
            return 0;
        }

        $server->run();

        return 0;
    }

    private function configureTcpServer(string $address, LanguageServerBuilder $builder): void
    {
        assert(is_string($address));
        $builder->tcpServer($address);
    }

    private function assertIsWritable(?string $filename = null): string
    {
        if (null === $filename) {
            $filename = 'language-server-request.log';
        }

        if (!file_exists(dirname($filename))) {
            throw new RuntimeException(sprintf('Directory "%s" does not exist', dirname($filename)));
        }

        if (file_exists($filename) && !is_writable($filename)) {
            throw new RuntimeException(sprintf('File at "%s" is not writable', $filename));
        }

        return $filename;
    }

    private function logMessage(OutputInterface $output, string $message): void
    {
        if ($output instanceof ConsoleOutput) {
            $output->getErrorOutput()->writeln(
                $message
            );
        }
    }
}
