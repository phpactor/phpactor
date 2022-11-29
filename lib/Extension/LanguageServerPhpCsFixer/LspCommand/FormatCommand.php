<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\LspCommand;

use Amp\Promise;
use Phpactor\Extension\LanguageServerPhpCsFixer\Model\PhpCsFixerProcess;
use Phpactor\Extension\LanguageServerPhpCsFixer\Util\DiffToTextEditsConverter;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\TextDocument\TextDocumentUri;
use Psr\Log\LoggerInterface;

class FormatCommand implements Command
{
    public function __construct(
        private PhpCsFixerProcess $phpCsFixer,
        private ClientApi $clientApi,
        private Workspace $workspace,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(string $uri, ?array $rules = null): Promise
    {
        return \Amp\call(function () use ($uri, $rules) {
            $path = TextDocumentUri::fromString($uri)->path();
            $textDocument = $this->workspace->get($uri);

            $rulesOpt = $rules ? ['--rules', ...$rules] : [];
            $diff = yield $this->phpCsFixer->fix($textDocument->text, ['--diff', '--dry-run', ...$rulesOpt]);

            $diffToTextEdits = new DiffToTextEditsConverter();
            $textEdits = $diffToTextEdits->toTextEdits($diff);

            $this->logger->debug(sprintf('PHP CS Fixer produced %s text edits', count($textEdits)));

            return $this->clientApi->workspace()->applyEdit(new WorkspaceEdit([
                $uri => $textEdits
            ]), 'Fix with PHP CS Fixer');
        });
    }
}
