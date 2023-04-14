<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\LspCommand;

use Amp\Promise;
use Phpactor\CodeTransform\Domain\Refactor\GenerateDecorator;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\LanguageServer\Core\Workspace\Workspace;

class GenerateDecoratorCommand implements Command
{
    public const NAME = 'generate_decorator';

    public function __construct(
        private ClientApi $clientApi,
        private Workspace $workspace,
        private GenerateDecorator $generateDecorator
    ) {
    }

    /**
     * @return Promise<ApplyWorkspaceEditResult>
     */
    public function __invoke(string $uri, string $interfaceFQN): Promise
    {
        $textDocument = $this->workspace->get($uri);
        $source = SourceCode::fromStringAndPath($textDocument->text, $textDocument->uri);

        $textEdits = $this->generateDecorator->getTextEdits($source, $interfaceFQN);

        return $this->clientApi->workspace()->applyEdit(new WorkspaceEdit([
            $uri => TextEditConverter::toLspTextEdits($textEdits, $textDocument->text)
        ]), 'Generate decoration');
    }
}
