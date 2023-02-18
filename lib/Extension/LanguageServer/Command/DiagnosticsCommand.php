<?php

namespace Phpactor\Extension\LanguageServer\Command;

use Amp\CancellationTokenSource;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use Phpactor\TextDocument\TextDocumentBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Amp\Promise\wait;

class DiagnosticsCommand extends Command
{
    public const NAME = 'language-server:diagnostics';

    public function __construct(private DiagnosticsProvider $provider)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Internal: resolve diagnostics in JSON for document provided over STDIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $textDocument = ProtocolFactory::textDocumentItem('undefined:///', $this->stdin());
        $diagnostics = wait(
            $this->provider->provideDiagnostics($textDocument, (new CancellationTokenSource())->getToken())
        );
        $output->write(json_encode($diagnostics));
        return 0;
    }

    private function stdin(): string
    {
        $in = '';

        while ($line = fgets(STDIN)) {
            $in .= $line;
        }

        return $in;
    }
}
