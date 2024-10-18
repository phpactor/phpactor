<?php

namespace Phpactor\Extension\LanguageServer\Command;

use Amp\CancellationTokenSource;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\Extension\LanguageServerWorseReflection\Workspace\WorkspaceIndex;
use Phpactor\LanguageServer\Core\Diagnostics\DiagnosticsProvider;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function Amp\Promise\wait;

class DiagnosticsCommand extends Command
{
    public const NAME = 'language-server:diagnostics';
    private const PARAM_URI = 'uri';


    public function __construct(
        private DiagnosticsProvider $provider,
        private WorkspaceIndex $workspace,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Internal: resolve diagnostics in JSON for document provided over STDIN');
        $this->addOption(self::PARAM_URI, null, InputOption::VALUE_REQUIRED, 'The URL for the document provided over STDIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $uri */
        $uri = $input->getOption(self::PARAM_URI) ?: 'untitled:///new';

        $textDocument = ProtocolFactory::textDocumentItem($uri, $this->stdin());

        // update the in-memory worse reflection workspace index so that we
        // can locate the latest function and class definitions in this process.
        $this->workspace->index(TextDocumentConverter::fromLspTextItem($textDocument));

        $diagnostics = wait(
            $this->provider->provideDiagnostics($textDocument, (new CancellationTokenSource())->getToken())
        );
        $decoded = json_encode($diagnostics);
        if (false === $decoded) {
            throw new RuntimeException(
                'Could not encode diagnostics',
            );
        }
        $output->write($decoded);
        return 0;
    }

    private function stdin(): string
    {
        $in = '';

        while (false !== $line = fgets(STDIN)) {
            $in .= $line;
        }

        return $in;
    }
}
