<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\LspCommand;

use Amp\Promise;
use Amp\Success;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeTransform\Domain\Refactor\PropertyAccessGenerator;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResult;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;

class PropertyAccessGeneratorCommand implements Command
{
    public function __construct(
        private readonly ClientApi $clientApi,
        private readonly Workspace $workspace,
        private readonly PropertyAccessGenerator $generateAccessor,
        private readonly string $editLabel
    ) {
    }

    /**
     * @param string[] $propertyNames
     * @return Promise<ApplyWorkspaceEditResult|null>
     */
    public function __invoke(string $uri, int $startOffset, array $propertyNames): Promise
    {
        $textDocument = $this->workspace->get($uri);

        try {
            $textEdits = $this->generateAccessor->generate(
                SourceCode::fromStringAndPath($textDocument->text, $textDocument->uri),
                $propertyNames,
                $startOffset
            );
        } catch (TransformException $error) {
            $this->clientApi->window()->showMessage()->warning($error->getMessage());
            return new Success(null);
        }

        return $this->clientApi->workspace()->applyEdit(new WorkspaceEdit([
             $uri => TextEditConverter::toLspTextEdits($textEdits, $textDocument->text)
        ]), $this->editLabel);
    }
}
