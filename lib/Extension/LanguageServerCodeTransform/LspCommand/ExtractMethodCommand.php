<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\LspCommand;

use Amp\Promise;
use Amp\Success;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeTransform\Domain\Refactor\ExtractMethod;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResult;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;

class ExtractMethodCommand implements Command
{
    public const NAME  = 'extract_method';
    public const DEFAULT_METHOD_NAME  = 'newMethod';

    public function __construct(
        private ClientApi $clientApi,
        private Workspace $workspace,
        private ExtractMethod $extractMethod
    ) {
    }

    /**
     * @return Promise<ApplyWorkspaceEditResult|null>
     */
    public function __invoke(string $uri, int $startOffset, int $endOffset): Promise
    {
        $textDocument = $this->workspace->get($uri);

        try {
            $textEdits = $this->extractMethod->extractMethod(
                SourceCode::fromStringAndPath($textDocument->text, $textDocument->uri),
                $startOffset,
                $endOffset,
                self::DEFAULT_METHOD_NAME
            );
        } catch (TransformException $error) {
            $this->clientApi->window()->showMessage()->warning($error->getMessage());
            return new Success(null);
        }

        return $this->clientApi->workspace()->applyEdit(new WorkspaceEdit([
             $uri => TextEditConverter::toLspTextEdits($textEdits->textEdits(), $textDocument->text)
        ]), 'Extract method');
    }
}
