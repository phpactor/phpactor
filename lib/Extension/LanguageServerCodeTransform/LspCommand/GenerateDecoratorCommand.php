<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\LspCommand;

use Amp\Promise;
use Phpactor\CodeTransform\Domain\Refactor\GenerateDecorator;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\CodeTransform\Domain\SourceCode;

class GenerateDecoratorCommand implements Command
{
    public const NAME = 'generate_decorator';

    private ClientApi $clientApi;

    private GenerateDecorator $generateDecorator;

    public function __construct(
        ClientApi $clientApi,
        GenerateDecorator $generateDecorator
    ) {
        $this->clientApi = $clientApi;
        $this->generateDecorator = $generateDecorator;
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
