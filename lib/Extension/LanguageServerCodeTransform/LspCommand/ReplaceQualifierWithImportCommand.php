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
use Phpactor\CodeTransform\Domain\Refactor\ReplaceQualifierWithImport;

class ReplaceQualifierWithImportCommand implements Command
{
    public const NAME = 'replace_qualifier_with_import';

    public function __construct(
        private ClientApi $clientApi,
        private Workspace $workspace,
        private ReplaceQualifierWithImport $replaceQualifierWithImport
    ) {
    }

    /**
     * @return Promise<?ApplyWorkspaceEditResponse>
     */
    public function __invoke(string $uri, int $offset): Promise
    {
        $textDocument = $this->workspace->get($uri);

        try {
            $textEdits = $this->replaceQualifierWithImport->getTextEdits(
                SourceCode::fromStringAndPath($textDocument->text, $textDocument->uri),
                $offset
            );
        } catch (TransformException $error) {
            $this->clientApi->window()->showMessage()->warning($error->getMessage());
            return new Success(null);
        }

        return $this->clientApi->workspace()->applyEdit(new WorkspaceEdit([
            $uri => TextEditConverter::toLspTextEdits($textEdits->textEdits(), $textDocument->text)
        ]), 'Expand Class');
    }
}
