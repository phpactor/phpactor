<?php

namespace Phpactor\Extension\LanguageServerCodeTransform\LspCommand;

use Amp\Promise;
use Amp\Success;
use Phpactor\CodeTransform\Domain\Exception\TransformException;
use Phpactor\CodeTransform\Domain\Refactor\ExtractExpression;
use Phpactor\CodeTransform\Domain\SourceCode;
use Phpactor\Extension\LanguageServerBridge\Converter\TextEditConverter;
use Phpactor\LanguageServerProtocol\ApplyWorkspaceEditResult;
use Phpactor\LanguageServerProtocol\WorkspaceEdit;
use Phpactor\LanguageServer\Core\Command\Command;
use Phpactor\LanguageServer\Core\Server\ClientApi;
use Phpactor\LanguageServer\Core\Workspace\Workspace;

class ExtractExpressionCommand implements Command
{
    public const NAME  = 'extract_expression';
    public const DEFAULT_VARIABLE_NAME  = 'newVariable';

    public function __construct(
        private readonly ClientApi $clientApi,
        private readonly Workspace $workspace,
        private readonly ExtractExpression $extractExpression
    ) {
    }

    /**
     * @return Promise<?ApplyWorkspaceEditResult>
     */
    public function __invoke(string $uri, int $startOffset, int $endOffset): Promise
    {
        $textDocument = $this->workspace->get($uri);

        try {
            $textEdits = $this->extractExpression->extractExpression(
                SourceCode::fromStringAndPath($textDocument->text, $textDocument->uri),
                $startOffset,
                $endOffset,
                self::DEFAULT_VARIABLE_NAME
            );
        } catch (TransformException $error) {
            $this->clientApi->window()->showMessage()->warning($error->getMessage());
            return new Success(null);
        }

        return $this->clientApi->workspace()->applyEdit(new WorkspaceEdit([
            $uri => TextEditConverter::toLspTextEdits($textEdits, $textDocument->text)
        ]), 'Extract expression');
    }
}
