<?php

namespace Phpactor\Extension\PhpCodeSniffer\LspCommand;

use Amp\Promise;
use Phpactor\Diff\DiffToTextEditsConverter;
use Phpactor\Extension\PhpCodeSniffer\Model\PhpCodeSnifferProcess;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResult;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextDocumentUri;
use Psr\Log\LoggerInterface;

class FormatCommand implements Command
{
    public function __construct(
        private PhpCodeSnifferProcess $phpCodeSniffer,
        private ClientApi $clientApi,
        private Workspace $workspace,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @return Promise<ApplyWorkspaceEditResult>
     */
    public function __invoke(string $uri): Promise
    {
        return \Amp\call(function () use ($uri) {
            $path = TextDocumentUri::fromString($uri)->path();
            $textDocument = $this->workspace->get($uri);

            $diff = yield $this->phpCodeSniffer->produceFixesDiff($textDocument);

            $diffToTextEdits = new DiffToTextEditsConverter();
            $textEdits = $diffToTextEdits->toTextEdits($diff);

            $this->logger->debug(sprintf('PHP Code Sniffer produced %s text edits', count($textEdits)));

            return $this->clientApi->workspace()->applyEdit(new WorkspaceEdit([
                $uri => $textEdits
            ]), 'Fix with PHP Code_Sniffer');
        });
    }
}
