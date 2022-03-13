<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\LspCommand;

use Amp\Promise;
use Amp\Success;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeTransform\Domain\Refactor\ExtractConstant;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResponse;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;

class ExtractConstantCommand implements Command
{
    public const NAME  = 'extract_constant';
    public const DEFAULT_VARIABLE_NAME  = 'NEW_CONSTANT';

    private ClientApi $clientApi;

    private Workspace $workspace;

    private ExtractConstant $extractConstant;

    public function __construct(
        ClientApi $clientApi,
        Workspace $workspace,
        ExtractConstant $extractConstant
    ) {
        $this->clientApi = $clientApi;
        $this->workspace = $workspace;
        $this->extractConstant = $extractConstant;
    }

    /**
     * @return Promise<?ApplyWorkspaceEditResponse>
     */
    public function __invoke(string $uri, int $offset): Promise
    {
        $textDocument = $this->workspace->get($uri);
        
        try {
            $textEdits = $this->extractConstant->extractConstant(
                SourceCode::fromStringAndPath($textDocument->text, $textDocument->uri),
                $offset,
                self::DEFAULT_VARIABLE_NAME
            );
        } catch (TransformException $error) {
            $this->clientApi->window()->showMessage()->warning($error->getMessage());
            return new Success(null);
        }
 
        return $this->clientApi->workspace()->applyEdit(new WorkspaceEdit([
            $uri => TextEditConverter::toLspTextEdits($textEdits->textEdits(), $textDocument->text)
        ]), 'Extract constant');
    }
}
