<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\LspCommand;

use Amp\Promise;
use Phpactor\CodeTransform\Domain\Refactor\GenerateDecorator;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;

class GenerateDecoratorCommand implements Command
{
    public const NAME = 'generate_decorator';

    private ClientApi $clientApi;

    private GenerateDecorator $generateDecorator;

    private Workspace $workspace;

    public function __construct(
        ClientApi $clientApi,
        Workspace $workspace,
        GenerateDecorator $generateDecorator
    ) {
        $this->clientApi = $clientApi;
        $this->generateDecorator = $generateDecorator;
        $this->workspace = $workspace;
    }

    /**
     * @return Promise<ApplyWorkspaceEditResponse>
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
