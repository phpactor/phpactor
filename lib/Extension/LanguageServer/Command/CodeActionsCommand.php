<?php

namespace Phpactor\Extension\LanguageServer\Command;

use Amp\CancellationTokenSource;
use Phpactor\Extension\LanguageServerBridge\Converter\TextDocumentConverter;
use Phpactor\Extension\LanguageServerWorseReflection\Workspace\WorkspaceIndex;
use Phpactor\LanguageServerProtocol\CodeActionParams;
use Phpactor\LanguageServer\Core\CodeAction\CodeActionProvider;
use Phpactor\LanguageServer\Test\ProtocolFactory;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function Amp\Promise\wait;

class CodeActionsCommand extends Command
{
    public const ARG_REQUEST = 'request';
    public const NAME = 'language-server:code-actions';

    public function __construct(
        private CodeActionProvider $provider,
        private WorkspaceIndex $workspace,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Internal: resolve code-actions asynchronously');
        $this->addArgument(self::ARG_REQUEST, InputArgument::REQUIRED, 'Code action LSP request');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $request */
        $request = $input->getArgument(self::ARG_REQUEST);

        $array = json_decode($request, true, JSON_THROW_ON_ERROR);
        if (!is_array($array)) {
            throw new RuntimeException(sprintf(
                'Expected json to decode to an array, got "%s"',
                get_debug_type($array)
            ));
        }
        /** @phpstan-ignore argument.type */
        $request = CodeActionParams::fromArray($array);
        $textDocumentItem = ProtocolFactory::textDocumentItem($request->textDocument->uri, $this->stdin());

        // update the in-memory worse reflection workspace index so that we
        // can locate the latest function and class definitions in this process.
        $this->workspace->index(TextDocumentConverter::fromLspTextItem($textDocumentItem));

        $diagnostics = wait(
            $this->provider->provideActionsFor(
                $textDocumentItem,
                $request->range,
                (new CancellationTokenSource())->getToken()
            )
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
