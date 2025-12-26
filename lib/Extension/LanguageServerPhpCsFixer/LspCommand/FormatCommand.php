<?php

namespace Phpactor\Extension\LanguageServerPhpCsFixer\LspCommand;

use function Amp\call;
use Amp\Promise;
use Phpactor\Diff\DiffToTextEditsConverter;
use Phpactor\Extension\LanguageServerPhpCsFixer\Model\PhpCsFixerProcess;
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
        private readonly PhpCsFixerProcess $phpCsFixer,
        private readonly ClientApi $clientApi,
        private readonly Workspace $workspace,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param  string[]|null  $rules  List of rules to use for formatting
     *
     * @return Promise<ApplyWorkspaceEditResult>
     */
    public function __invoke(string $uri, ?array $rules = null): Promise
    {
        return call(function () use ($uri, $rules) {
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
