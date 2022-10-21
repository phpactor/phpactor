<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\LspCommand;

use Amp\Promise;
use Amp\Success;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;
use Phpactor\CodeTransform\Domain\Refactor\SimplifyClassName;

class SimplifyClassNameCommand implements Command
{
    public const NAME = 'simplify_class';

    private ClientApi $clientApi;

    private Workspace $workspace;

    private SimplifyClassName $simplifyClassName;

    public function __construct(
        ClientApi $clientApi,
        Workspace $workspace,
        SimplifyClassName $simplifyClassName
    ) {
        $this->clientApi = $clientApi;
        $this->workspace = $workspace;
        $this->simplifyClassName = $simplifyClassName;
    }

    /**
     * @return Promise<?ApplyWorkspaceEditResponse>
     */
    public function __invoke(string $uri, int $offset): Promise
    {
        $textDocument = $this->workspace->get($uri);

        try {
            $textEdits = $this->simplifyClassName->getTextEdits(
                SourceCode::fromStringAndPath($textDocument->text, $textDocument->uri),
                $offset
            );
        } catch (TransformException $error) {
            $this->clientApi->window()->showMessage()->warning($error->getMessage());
            return new Success(null);
        }

        return $this->clientApi->workspace()->applyEdit(new WorkspaceEdit([
            $uri => TextEditConverter::toLspTextEdits($textEdits, $textDocument->text)
        ]), 'Expand Class');
    }
}
